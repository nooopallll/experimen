<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Customer;
use App\Models\Member;
use App\Models\Treatment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * 1. HALAMAN MANAJEMEN PESANAN (INDEX)
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'details'])->latest();

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function($q) use ($keyword) {
                $q->where('no_invoice', 'like', "%{$keyword}%")
                  ->orWhereHas('customer', function($c) use ($keyword) {
                      $c->where('nama', 'like', "%{$keyword}%")
                        ->orWhere('no_hp', 'like', "%{$keyword}%");
                  });
            });
        }

        $orders = $query->paginate(10);

        if ($request->ajax()) {
            return view('pesanan.partials.list', compact('orders'))->render();
        }

        return view('pesanan.index', compact('orders')); 
    }

    /**
     * MENAMPILKAN DETAIL PESANAN
     */
    public function show($id)
    {
        $order = Order::with(['customer', 'details'])->findOrFail($id);
        $treatments = Treatment::orderBy('nama_treatment', 'asc')->get();

        return view('pesanan.show', compact('order', 'treatments'));
    }

    /**
     * UPDATE DATA UTAMA & DETAIL PESANAN
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_customer' => 'required|string',
            'status' => 'required|string',
            'details' => 'array' 
        ]);

        try {
            DB::beginTransaction();
            
            $order = Order::findOrFail($id);
            $oldStatus = $order->status_order;
            $targetStatus = $request->status;
            
            $order->status_order = $targetStatus;
            $order->catatan = $request->catatan; 
            $order->kasir_keluar = $request->kasir_keluar ?? null;
            
            if ($order->customer) {
                $order->customer->nama = $request->nama_customer;
                $order->customer->save();
            }

            $statusChanged = ($oldStatus !== $targetStatus);
            $isFinalStatus = in_array($targetStatus, ['Selesai', 'Diambil', 'Batal']);
            $shouldForceItems = $statusChanged && $isFinalStatus;

            if ($request->has('details')) {
                $totalHargaBaru = 0;

                foreach ($request->details as $detailId => $data) {
                    $detail = OrderDetail::find($detailId);
                    
                    if ($detail && $detail->order_id == $order->id) {
                        $detail->nama_barang = $data['nama_barang'] ?? $detail->nama_barang;
                        $detail->layanan = $data['layanan'] ?? $detail->layanan;
                        $detail->estimasi_keluar = $data['estimasi_keluar'] ?? $detail->estimasi_keluar;
                        $detail->harga = (int) ($data['harga'] ?? $detail->harga);

                        if ($shouldForceItems) {
                            $detail->status = $targetStatus;
                        } else {
                            $detail->status = $data['status'] ?? $detail->status;
                        }
                        
                        $detail->save();
                        $totalHargaBaru += $detail->harga; 
                    }
                }
                $order->total_harga = $totalHargaBaru;

                // Logika Cerdas: Update Status Order berdasarkan Item
                $allItems = $order->details()->get();
                $totalItem = $allItems->count();
                
                if ($totalItem > 0) {
                    $countDiambil = $allItems->where('status', 'Diambil')->count();
                    $countSelesaiOrDiambil = $allItems->whereIn('status', ['Selesai', 'Diambil'])->count();
                    
                    if ($countDiambil == $totalItem) {
                        $order->status_order = 'Diambil';
                    } elseif ($countSelesaiOrDiambil == $totalItem) {
                        if ($targetStatus !== 'Batal') {
                            $order->status_order = 'Selesai';
                        }
                    } else {
                        if ($targetStatus !== 'Batal') {
                            $order->status_order = 'Proses';
                        }
                    }
                }
            }

            $order->save();
            DB::commit();
            return back()->with('success', 'Status pesanan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * 2. CEK STATUS CUSTOMER SEBELUM ORDER
     */
    public function check(Request $request)
    {
        $customer = Customer::where('no_hp', $request->no_hp)->with('member')->first();
        $treatments = Treatment::orderBy('nama_treatment', 'asc')->get(); 

        $data = [
            'no_hp' => $request->no_hp,
            'customer' => null,
            'status' => 'New Customer',
            'color' => 'text-blue-500 bg-blue-50 border-blue-200',
            'is_member' => false,
            'poin' => 0,
            'treatments' => $treatments 
        ];

        if ($customer) {
            $data['customer'] = $customer;
            $data['no_hp'] = $customer->no_hp;
            
            if ($customer->member) {
                $data['status'] = 'MEMBER';
                $data['color'] = 'text-pink-600 bg-pink-100 border-pink-200';
                $data['is_member'] = true;
                $data['poin'] = $customer->member->poin;
            } else {
                $data['status'] = 'Repeat Order';
                $data['color'] = 'text-green-600 bg-green-100 border-green-200';
                $data['is_member'] = false;
            }
        }

        return view('input-order', $data);
    }

    /**
     * 3. SIMPAN ORDER (CORE FUNCTION - DIPERBAIKI)
     * Menangani JSON Response, Poin, dan Item Kosong
     */
    public function store(Request $request)
    {
        // 1. FILTERISASI MANUAL: Hapus baris item yang kosong sebelum validasi
        // Ini mencegah error "Field is required" jika user menambah baris tapi tidak mengisinya
        $rawItems = $request->item ?? [];
        $rawPrices = $request->harga ?? [];
        $rawServices = $request->kategori_treatment ?? [];
        $rawDates = $request->tanggal_keluar ?? [];
        $rawNotes = $request->catatan ?? [];
        
        $validIndexes = [];
        if(is_array($rawItems)) {
            foreach($rawItems as $index => $val) {
                // Hanya proses jika nama barang tidak kosong
                if(!empty($val)) {
                    $validIndexes[] = $index;
                }
            }
        }

        // Cek apakah ada minimal 1 item valid
        if (empty($validIndexes)) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Harap isi minimal satu item sepatu.'], 422);
            }
            return back()->with('error', 'Harap isi minimal satu item sepatu.');
        }

        // 2. Validasi Data Utama (Customer)
        $validator = Validator::make($request->all(), [
            'nama_customer' => 'required',
            'no_hp' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // 3. Handle Customer (Create or Update)
            $customer = Customer::firstOrCreate(
                ['no_hp' => $request->no_hp],
                ['nama' => $request->nama_customer]
            );

            if($customer->nama !== $request->nama_customer) {
                $customer->update(['nama' => $request->nama_customer]);
            }

            // 4. Logika Klaim Poin (Reward)
            $discountAmount = 0;
            $claimNote = "";
            
            // Cek apakah member eligible dan memilih klaim (Diskon / Parfum)
            if ($customer->member && $customer->member->poin >= 8 && $request->filled('claim_type')) {
                if ($request->claim_type === 'diskon') {
                    $discountAmount = 10000;
                    $customer->member->decrement('poin', 8); // KURANGI POIN
                    $claimNote = "[KLAIM DISKON 10rb]";
                } elseif ($request->claim_type === 'parfum') {
                    $customer->member->decrement('poin', 8); // KURANGI POIN
                    $claimNote = "[KLAIM FREE PARFUM]";
                }
            }

            // 5. Generate Invoice
            // Hitung total order di database untuk nomor urut
            $count = Order::count() + 1;
            // Format: INV-YYYYMMDD-00X
            $invoice = 'INV-' . date('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            // 6. Hitung Total Harga (Hanya dari item yang valid)
            $totalHarga = 0;
            foreach ($validIndexes as $i) {
                $h = $rawPrices[$i] ?? 0;
                // Bersihkan format Rupiah (titik/koma) jadi integer
                $totalHarga += (int) preg_replace('/[^0-9]/', '', $h);
            }

            // Kurangi dengan diskon (jika ada)
            $finalTotal = $totalHarga - $discountAmount;

            // 7. Tentukan Status Pembayaran
            $statusPembayaran = $request->status_pembayaran ?? 'Belum Lunas';
            $metodePembayaran = $request->metode_pembayaran ?? 'Tunai';
            $inputPaidAmount = $request->paid_amount ? (int) preg_replace('/[^0-9]/', '', $request->paid_amount) : 0;
            
            $jumlahBayar = 0;
            if ($statusPembayaran == 'Lunas') {
                // Jika lunas, bayar full atau sesuai input user jika lebih besar
                $jumlahBayar = ($inputPaidAmount > 0) ? $inputPaidAmount : $finalTotal;
            } elseif ($statusPembayaran == 'DP') {
                $jumlahBayar = $inputPaidAmount;
            } else {
                $jumlahBayar = 0;
                $metodePembayaran = null; 
            }

            // 8. Simpan Data Order Utama
            $order = Order::create([
                'no_invoice' => $invoice,
                'customer_id' => $customer->id,
                'tgl_masuk' => now(),
                'total_harga' => $finalTotal,
                'paid_amount' => $jumlahBayar,
                'metode_pembayaran' => $metodePembayaran,
                'status_pembayaran' => $statusPembayaran,
                'status_order' => 'Proses',
                'tipe_customer' => $request->tipe_customer,
                'sumber_info' => $request->sumber_info,
                // Gabungkan catatan klaim reward dengan catatan customer (ambil catatan baris pertama sebagai perwakilan)
                'catatan' => trim($claimNote . " " . ($rawNotes[0] ?? '-')),
                'kasir' => $request->cs ?? 'Admin',
            ]);

            // 9. Simpan Detail Item (Looping index valid)
            foreach ($validIndexes as $i) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'nama_barang' => $rawItems[$i],
                    'layanan' => $rawServices[$i] ?? 'General',
                    'harga' => (int) preg_replace('/[^0-9]/', '', $rawPrices[$i]),
                    'estimasi_keluar' => $rawDates[$i] ?? null,
                    'catatan' => $rawNotes[$i] ?? null,
                    'status' => 'Proses',
                ]);
            }

            // 10. Tambah Poin Transaksi Baru (1 Poin per 50rb)
            if ($customer->member) {
                $customer->member->increment('total_transaksi', $totalHarga);
                $poinBaru = floor($totalHarga / 50000);
                if ($poinBaru > 0) {
                    $customer->member->increment('poin', $poinBaru);
                }
            }

            DB::commit();

            // === RESPON JSON UNTUK AJAX (POPUP INVOICE) ===
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Order berhasil disimpan!',
                    // Load relasi agar data customer & detail muncul di Invoice JS
                    'order' => $order->load('customer', 'details'),
                    'discount_amount' => $discountAmount,
                    'original_total' => $totalHarga
                ]);
            }

            // Fallback jika submit biasa (non-ajax)
            return redirect()->route('orders.invoice', $order->id)->with('success', 'Order berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            // Respon error JSON jika AJAX
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * MENAMPILKAN HALAMAN INVOICE (NON-POPUP / Direct Link)
     */
    public function invoice($id)
    {
        $order = Order::with(['customer', 'details'])->findOrFail($id);
        return view('orders.invoice', compact('order'));
    }

    /**
     * FUNGSI UPDATE STATUS PER ITEM
     */
    public function updateDetail(Request $request, $id)
    {
        $detail = OrderDetail::findOrFail($id);
        
        if ($request->has('status')) {
            $detail->status = $request->status;
            $detail->save();
        }

        // Cek status keseluruhan order (Smart Status Update)
        $order = $detail->order;
        $itemBelumSelesai = $order->details()
            ->whereNotIn('status', ['Selesai', 'Diambil'])
            ->count();

        if ($itemBelumSelesai == 0) {
            $itemBukanDiambil = $order->details()
                ->where('status', '!=', 'Diambil')
                ->count();

            if ($itemBukanDiambil == 0) {
                $order->status_order = 'Diambil';
            } else {
                $order->status_order = 'Selesai';
            }
        } else {
            $order->status_order = 'Proses';
        }
        
        $order->save();

        return back()->with('success', 'Status berhasil diperbarui');
    }

    /**
     * FUNGSI TOGGLE WA MARKER
     */
    public function toggleWa($id, $type)
    {
        $order = Order::findOrFail($id);
        
        if ($type == 1) {
            $order->wa_sent_1 = !$order->wa_sent_1;
        } elseif ($type == 2) {
            $order->wa_sent_2 = !$order->wa_sent_2;
        }
        
        $order->save();
        return back();
    }

    /**
     * FUNGSI AJAX CEK CUSTOMER & POIN
     */
    public function checkCustomer(Request $request)
    {
        $customer = Customer::with('member')
                    ->where('no_hp', $request->no_hp)
                    ->first();

        if ($customer) {
            $poin = $customer->member ? $customer->member->poin : 0;
            $targetPoin = 8; 
            
            return response()->json([
                'found' => true,
                'nama' => $customer->nama,
                'tipe' => $customer->member ? 'Member' : 'Repeat Order',
                'poin' => $poin,
                'target' => $targetPoin, 
                'bisa_claim' => $poin >= $targetPoin,
                'member_id' => $customer->member ? $customer->member->id : null,
            ]);
        }

        return response()->json(['found' => false]);
    }
}