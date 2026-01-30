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
     * Menampilkan daftar order dengan Relasi Customer & Details
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

        // === LOGIKA POPUP INVOICE ===
        // Menangkap invoice_id dari URL untuk menampilkan modal otomatis
        $popupOrder = null;
        if ($request->has('invoice_id')) {
            $popupOrder = Order::with(['customer', 'details'])->find($request->invoice_id);
        }

        return view('pesanan.index', compact('orders', 'popupOrder')); 
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
     * Update: Menambahkan Logika Diskon & Fix Redirect Invoice
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

            // 3. Hitung Total Harga Awal (Sebelum Diskon)
            $totalHarga = 0;
            if (is_array($request->harga)) {
                $totalHarga = array_sum(array_map(function ($h) {
                    return (int) preg_replace('/[^0-9]/', '', $h);
                }, $request->harga));
            }

            // === LOGIKA REWARD DARI INPUT ORDER ===
            $discountAmount = 0;
            $extraItemName = null;
            $jenisKlaim = null; // Variable untuk kolom 'klaim'

            // Cek apakah user meminta tukar poin (is_claim == "1") dan punya member
            // Note: Pastikan di view input hidden namenya 'is_claim' sesuai script JS sebelumnya, atau sesuaikan disini.
            // Jika script JS pakai 'is_claim', gunakan $request->is_claim. Jika 'tukar_poin', gunakan $request->tukar_poin.
            // Di sini saya pakai $request->is_claim agar sinkron dengan script view terakhir yg saya berikan.
            $isClaim = $request->is_claim ?? $request->tukar_poin; 

            if ($isClaim == "1" && $customer->member) {
                // Pastikan poin cukup
                if ($customer->member->poin >= 8) {
                    
                    // Ambil Tipe Reward dari Input Hidden
                    $rewardType = $request->reward_type; // Contoh: 'diskon' atau 'Parfum Sepatu'

                    if ($rewardType === 'diskon') {
                        // KASUS 1: DISKON
                        $discountAmount = 35000;
                        $jenisKlaim = 'Diskon'; 
                    } else {
                        // KASUS 2: BARANG (Parfum/Merchandise)
                        $extraItemName = $rewardType;
                        $jenisKlaim = $rewardType; 
                    }

                    // Potong 8 Poin
                    $customer->member->decrement('poin', 8); 
                }
            }

            // Hitung Total Akhir
            $totalHargaFinal = $totalHarga - $discountAmount;
            if ($totalHargaFinal < 0) $totalHargaFinal = 0;

            // 4. Generate Invoice
            $count = Order::whereDate('created_at', today())->count() + 1;
            $invoice = 'INV-' . date('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            // 5. Logika Pembayaran
            $statusPembayaran = $request->status_pembayaran ?? 'Belum Lunas';
            $metodePembayaran = $request->metode_pembayaran ?? 'Tunai';
            $inputPaidAmount = $request->paid_amount ? (int) preg_replace('/[^0-9]/', '', $request->paid_amount) : 0;
            
            $jumlahBayar = 0;
            if ($statusPembayaran == 'Lunas') {
                $jumlahBayar = ($inputPaidAmount > 0) ? $inputPaidAmount : $totalHargaFinal;
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
                'total_harga' => $totalHargaFinal, // Harga Setelah Diskon
                'discount' => $discountAmount,      // Simpan Nilai Diskon
                'paid_amount' => $jumlahBayar,
                'metode_pembayaran' => $metodePembayaran,
                'status_pembayaran' => $statusPembayaran,
                'status_order' => 'Proses',
                'tipe_customer' => $request->tipe_customer,
                'sumber_info' => $request->sumber_info,
                'catatan' => $request->catatan[0] ?? '-',
                'kasir' => $request->cs ?? 'Admin',
            ]);

            // 7. Simpan Detail Item (Looping Item Inputan)
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
                            'klaim' => null // Item biasa bukan klaim
                        ]);
                    }
                }
            }

            // 7b. Simpan Item Reward (Jika Ada - Khusus Barang)
            if ($extraItemName) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'nama_barang' => $extraItemName, // Nama barang reward (misal: Parfum Sepatu)
                    'layanan' => 'Member Reward',
                    'harga' => 0, // Gratis
                    'estimasi_keluar' => now(), // Langsung ada/selesai
                    'catatan' => 'Hadiah Poin',
                    'status' => 'Diambil',
                    'klaim' => $jenisKlaim // Isi kolom klaim di database
                ]);
            }

            // 8. Update Poin Member (Dapat Poin Baru dari Transaksi)
            if ($customer->member) {
                $customer->member->increment('total_transaksi', $totalHargaFinal);
                // Hitung poin yang didapat (misal per 50rb dapat 1 poin)
                $poinBaru = floor($totalHargaFinal / 50000);
                if ($poinBaru > 0) {
                    $customer->member->increment('poin', $poinBaru);
                }
            }

            DB::commit();
            
            // === [PENTING] REDIRECT DENGAN PARAMETER INVOICE_ID ===
            // Ini kuncinya agar popup invoice muncul
            return redirect()->route('pesanan.index', ['invoice_id' => $order->id])
                ->with('success', 'Order berhasil! ' . $invoice);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
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
     * UPDATE PESANAN (EDIT)
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
            
            // Update Data Utama
            $order->status_order = $request->status;
            $order->catatan = $request->catatan; 
            $order->kasir_keluar = $request->kasir_keluar; 
            
            // Update Nama Customer
            if ($order->customer) {
                $order->customer->nama = $request->nama_customer;
                $order->customer->save();
            }

            // Update Detail Item
            if ($request->has('details')) {
                $totalHargaBaru = 0;
                foreach ($request->details as $detailId => $data) {
                    $detail = OrderDetail::find($detailId);
                    if ($detail && $detail->order_id == $order->id) {
                        $detail->nama_barang = $data['nama_barang'] ?? $detail->nama_barang;
                        $detail->layanan = $data['layanan'] ?? $detail->layanan;
                        $detail->estimasi_keluar = $data['estimasi_keluar'] ?? $detail->estimasi_keluar;
                        $detail->status = $data['status'] ?? $detail->status;
                        $detail->harga = (int) ($data['harga'] ?? $detail->harga);
                        $detail->save();
                        $totalHargaBaru += $detail->harga;
                    }
                }
                // Update total harga dengan tetap menghitung diskon yang sudah ada
                $potongan = $order->discount ?? 0;
                $order->total_harga = $totalHargaBaru - $potongan; 
            }

            $order->save();
            DB::commit();
            return back()->with('success', 'Perubahan berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * FUNGSI UPDATE STATUS PER ITEM (AJAX/DIRECT)
     */
    public function updateDetail(Request $request, $id)
    {
        $detail = OrderDetail::findOrFail($id);
        if ($request->has('status')) {
            $detail->status = $request->status;
            $detail->save();
        }

        // Cek Auto Complete Order
        $order = $detail->order;
        $itemBelumSelesai = $order->details()
            ->whereNotIn('status', ['Selesai', 'Diambil'])
            ->count();

        if ($itemBelumSelesai == 0) {
            $order->status_order = 'Selesai';
        } else {
            $order->status_order = 'Proses';
        }
        $order->save();

        return back()->with('success', 'Status item diperbarui');
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
     * FUNGSI AJAX CEK CUSTOMER (Untuk Input Order)
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