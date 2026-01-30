<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Customer;
use App\Models\Member;
use App\Models\Treatment;
use Illuminate\Support\Facades\DB;

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

        // === TAMBAHAN LOGIKA LIVE SEARCH ===
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
     * (Bagian ini diperbarui untuk sinkronisasi status otomatis)
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
            $oldStatus = $order->status_order; // Status sebelum diedit
            $targetStatus = $request->status;  // Status baru dari dropdown
            
            // 1. Update Data Utama
            $order->status_order = $targetStatus;
            $order->catatan = $request->catatan; 
            $order->kasir_keluar = $request->kasir_keluar ?? null;
            
            // 2. Update Nama Customer
            if ($order->customer) {
                $order->customer->nama = $request->nama_customer;
                $order->customer->save();
            }

            // === LOGIKA CERDAS: TOP-DOWN ===
            // Kita hanya memaksa ubah semua item jika status UTAMA benar-benar BERUBAH (misal: Proses -> Selesai).
            // Jika status utama tidak diubah (tetap "Selesai"), kita biarkan user mengatur per item.
            $statusChanged = ($oldStatus !== $targetStatus);
            $isFinalStatus = in_array($targetStatus, ['Selesai', 'Diambil', 'Batal']);
            
            $shouldForceItems = $statusChanged && $isFinalStatus;

            // 3. Update Rincian Layanan
            if ($request->has('details')) {
                $totalHargaBaru = 0;

                foreach ($request->details as $detailId => $data) {
                    $detail = OrderDetail::find($detailId);
                    
                    if ($detail && $detail->order_id == $order->id) {
                        // Update Data Barang
                        $detail->nama_barang = $data['nama_barang'] ?? $detail->nama_barang;
                        $detail->layanan = $data['layanan'] ?? $detail->layanan;
                        $detail->estimasi_keluar = $data['estimasi_keluar'] ?? $detail->estimasi_keluar;
                        $detail->harga = (int) ($data['harga'] ?? $detail->harga);

                        // Cek apakah harus memaksa status item?
                        if ($shouldForceItems) {
                            $detail->status = $targetStatus; // Ikuti Status Utama
                        } else {
                            $detail->status = $data['status'] ?? $detail->status; // Ikuti Input Per Item
                        }
                        
                        $detail->save();
                        $totalHargaBaru += $detail->harga; 
                    }
                }
                $order->total_harga = $totalHargaBaru;

                // === LOGIKA CERDAS: BOTTOM-UP (Auto Status) ===
                // Cek kondisi item terbaru untuk menentukan status order otomatis
                
                // Refresh data item agar mendapat status terbaru yang baru saja disimpan
                $allItems = $order->details()->get();
                $totalItem = $allItems->count();
                
                if ($totalItem > 0) {
                    $countDiambil = $allItems->where('status', 'Diambil')->count();
                    $countSelesaiOrDiambil = $allItems->whereIn('status', ['Selesai', 'Diambil'])->count();
                    
                    // Skenario 1: Semua item sudah "Diambil" -> Status Order WAJIB "Diambil"
                    if ($countDiambil == $totalItem) {
                        $order->status_order = 'Diambil';
                    } 
                    // Skenario 2: Semua item sudah "Selesai" (atau campur Diambil) -> Status Order "Selesai"
                    elseif ($countSelesaiOrDiambil == $totalItem) {
                        // Jangan ubah jika user secara eksplisit set ke "Batal"
                        if ($targetStatus !== 'Batal') {
                            $order->status_order = 'Selesai';
                        }
                    } 
                    // Skenario 3: Masih ada yang Proses -> Status Order "Proses"
                    else {
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
     * 2. CEK STATUS CUSTOMER SEBELUM ORDER (Halaman Input)
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
     * 3. SIMPAN ORDER (CORE FUNCTION)
     */
    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'nama_customer' => 'required',
            'no_hp' => 'required',
            'item.*' => 'required',
            'harga.*' => 'required', 
        ]);

        try {
            DB::beginTransaction();

            // 2. Simpan/Cari Customer
            $customer = Customer::firstOrCreate(
                ['no_hp' => $request->no_hp],
                ['nama' => $request->nama_customer]
            );

            if($customer->nama !== $request->nama_customer) {
                $customer->update(['nama' => $request->nama_customer]);
            }

            // 3. Generate Invoice
            $count = Order::whereDate('created_at', today())->count() + 1;
            $invoice = 'INV-' . date('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            // 4. Hitung Total Harga
            $totalHarga = 0;
            if (is_array($request->harga)) {
                $totalHarga = array_sum(array_map(function ($h) {
                    return (int) preg_replace('/[^0-9]/', '', $h);
                }, $request->harga));
            }

            // 5. Logika Pembayaran
            $statusPembayaran = $request->status_pembayaran ?? 'Belum Lunas';
            $metodePembayaran = $request->metode_pembayaran ?? 'Tunai';
            
            $inputPaidAmount = $request->paid_amount ? (int) preg_replace('/[^0-9]/', '', $request->paid_amount) : 0;
            
            $jumlahBayar = 0;
            if ($statusPembayaran == 'Lunas') {
                $jumlahBayar = ($inputPaidAmount > 0) ? $inputPaidAmount : $totalHarga;
            } elseif ($statusPembayaran == 'DP') {
                $jumlahBayar = $inputPaidAmount;
            } else {
                $jumlahBayar = 0;
                $metodePembayaran = null; 
            }

            // 6. Simpan Order Utama
            $order = Order::create([
                'no_invoice' => $invoice,
                'customer_id' => $customer->id,
                'tgl_masuk' => now(),
                'total_harga' => $totalHarga,
                'paid_amount' => $jumlahBayar,
                'metode_pembayaran' => $metodePembayaran,
                'status_pembayaran' => $statusPembayaran,
                'status_order' => 'Proses',
                'tipe_customer' => $request->tipe_customer,
                'sumber_info' => $request->sumber_info,
                'catatan' => $request->catatan[0] ?? '-',
                'kasir' => $request->cs ?? 'Admin',
            ]);

            // 7. Simpan Detail Item
            $items = $request->item;
            if (is_array($items)) {
                for ($i = 0; $i < count($items); $i++) {
                    if (!empty($items[$i])) {
                        $hargaRaw = $request->harga[$i] ?? 0;
                        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $hargaRaw);

                        OrderDetail::create([
                            'order_id' => $order->id,
                            'nama_barang' => $items[$i],
                            'layanan' => $request->kategori_treatment[$i] ?? 'General',
                            'harga' => $hargaBersih,
                            'estimasi_keluar' => $request->tanggal_keluar[$i] ?? null,
                            'catatan' => $request->catatan[$i] ?? null,
                            'status' => 'Proses',
                        ]);
                    }
                }
            }

            // 8. Update Poin Member
            if ($customer->member) {
                $customer->member->increment('total_transaksi', $totalHarga);
                $poinBaru = floor($totalHarga / 50000);
                if ($poinBaru > 0) {
                    $customer->member->increment('poin', $poinBaru);
                }
            }

            DB::commit();
            return redirect()->route('pesanan.index')->with('success', 'Order berhasil! ' . $invoice);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
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

        $order = $detail->order;
        $itemBelumSelesai = $order->details()
            ->whereNotIn('status', ['Selesai', 'Diambil'])
            ->count();

        if ($itemBelumSelesai == 0) {
            // Logika Cerdas yang sama: Cek apakah semua "Diambil"?
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
     * FUNGSI TOGGLE WA
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
     * FUNGSI AJAX CEK CUSTOMER
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