<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Customer;
use App\Models\Member;
use App\Models\Treatment; // <--- WAJIB DITAMBAHKAN
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
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
        return view('pesanan.index', compact('orders'));
    }

    // --- FUNGSI HALAMAN UTAMA (Perbaikan Data Treatment) ---
    public function check(Request $request)
    {
        $customer = Customer::where('no_hp', $request->no_hp)->with('member')->first();
        
        // AMBIL DATA TREATMENT (Ini yang sebelumnya kurang)
        $treatments = Treatment::orderBy('nama_treatment', 'asc')->get(); 

    public function check(Request $request)
    {
        $customer = Customer::where('no_hp', $request->no_hp)->with('member')->first();
        $data = [
            'no_hp' => $request->no_hp,
            'customer' => null,
            'status' => 'New Customer',
            'color' => 'text-blue-500 bg-blue-50 border-blue-200',
            'is_member' => false,
            'poin' => 0,
            'treatments' => $treatments // <--- KIRIM KE VIEW
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
                // Perbaikan text status awal
                $data['status'] = 'Repeat Order'; 
                $data['status'] = 'Repeat Order';
                $data['color'] = 'text-green-600 bg-green-100 border-green-200';
                $data['is_member'] = false;
            }
        }

        return view('input-order', $data);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nama_customer' => 'required',
            'no_hp' => 'required',
            'item.*' => 'required',
            'harga.*' => 'required',
        ]);

        try {
            DB::beginTransaction();

            // 2. Buat/Ambil Customer
            $customer = Customer::firstOrCreate(
                ['no_hp' => $request->no_hp],
                ['nama' => $request->nama_customer]
            );

            // 3. Generate Invoice
            $count = Order::whereDate('created_at', today())->count() + 1;
            $invoice = 'INV-' . date('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            // 4. Hitung Total Harga
            $totalHarga = 0;
            if(is_array($request->harga)){
                 $totalHarga = array_sum(array_map(function($h) {
                     return (int) preg_replace('/[^0-9]/', '', $h); 
                 }, $request->harga));
            }

            $order = Order::create([
                'no_invoice' => $invoice,
                'customer_id' => $customer->id,
                'tgl_masuk' => now(),
                'total_harga' => $totalHarga,
                'status_pembayaran' => $request->pembayaran,
                'status_order' => 'Proses',
                'tipe_customer' => $request->tipe_customer,
                'sumber_info' => $request->sumber_info,
                'catatan' => $request->catatan[0] ?? '-',
                'kasir' => $request->cs ?? 'Admin',
            ]);

            $items = $request->item;
            for ($i = 0; $i < count($items); $i++) {
                if (!empty($items[$i])) {
                    $hargaRaw = $request->harga[$i] ?? 0;
                    $hargaBersih = (int) preg_replace('/[^0-9]/', '', $hargaRaw);
        // ... Loop Simpan Detail Item (Sama seperti sebelumnya) ...
        $items = $request->item;
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
                    'status' => 'Proses',
                ]);
            }

            if ($customer->member) {
                $customer->member->increment('total_transaksi', $totalHarga);
                $poinBaru = floor($totalHarga / 50000);
                $customer->member->increment('poin', $poinBaru);
            }

            DB::commit();
            return redirect()->route('pesanan.index')->with('success', 'Order berhasil diinput!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    // --- FUNGSI AJAX CEK CUSTOMER (Perbaikan Status Repeat Order) ---
    public function checkCustomer(Request $request)
    {
        $customer = \App\Models\Customer::with('member')
                    ->where('no_hp', $request->no_hp)
                    ->first();

        if ($customer) {
            $poin = $customer->member ? $customer->member->poin : 0;
            $targetPoin = 8; 
            
            return response()->json([
                'found' => true,
                'nama' => $customer->nama,
                // PERBAIKAN PENTING DI SINI:
                'tipe' => $customer->member ? 'Member' : 'Repeat Order', 
                'poin' => $poin,
                'target' => $targetPoin, 
                'bisa_claim' => $poin >= $targetPoin, 
                'member_id' => $customer->member ? $customer->member->id : null,
            ]);
        }

        return response()->json(['found' => false]);
    }
    
    // ... (Fungsi toggleWa dan updateDetail biarkan saja, sudah aman)
    public function toggleWa($id, $type)
    {
            // LOGIKA PEMBAYARAN BARU (LEBIH SEDERHANA & KUAT)
            // Langsung ambil dari input karena View sudah memisahkan inputnya
            $statusPembayaran = $request->status_pembayaran ?? 'Belum Lunas';
            $metodePembayaran = $request->metode_pembayaran ?? 'Tunai';

            // Bersihkan format rupiah dari input paid_amount
            $inputPaidAmount = $request->paid_amount ? (int) preg_replace('/[^0-9]/', '', $request->paid_amount) : 0;
            $jumlahBayar = 0;

            if ($statusPembayaran == 'Lunas') {
                // Jika lunas, bayar full (atau sesuai input jika ada)
                $jumlahBayar = ($inputPaidAmount > 0) ? $inputPaidAmount : $totalHarga;
            } elseif ($statusPembayaran == 'DP') {
                // Jika DP, wajib pakai inputan user
                $jumlahBayar = $inputPaidAmount;
            } else {
                // Belum Lunas
                $jumlahBayar = 0;
                $metodePembayaran = null; 
            }

            // 6. Simpan Data Order Utama (Disini variabel $order DIBUAT)
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

            // 7. Simpan Detail Item (Looping SETELAH $order dibuat)
            $items = $request->item;
            if (is_array($items)) {
                for ($i = 0; $i < count($items); $i++) {
                    if (!empty($items[$i])) {
                        $hargaRaw = $request->harga[$i] ?? 0;
                        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $hargaRaw);

                        OrderDetail::create([
                            'order_id' => $order->id, // Sekarang aman, $order sudah ada
                            'nama_barang' => $items[$i],
                            'layanan' => $request->kategori_treatment[$i] ?? 'General',
                            'harga' => $hargaBersih,
                            'estimasi_keluar' => $request->tanggal_keluar[$i] ?? null, // Perbaiki nama field (estimasi_keluar vs tanggal_keluar)
                            'catatan' => $request->catatan[$i] ?? null,
                            'status' => 'Proses',
                        ]);
                    }
                }
            }

            // 8. Update Poin Member (Jika ada)
            if ($customer->member) {
                $customer->member->increment('total_transaksi', $totalHarga);
                // Hitung poin: kelipatan 50.000 dapat 1 poin (contoh)
                $poinBaru = floor($totalHarga / 50000);
                if ($poinBaru > 0) {
                    $customer->member->increment('poin', $poinBaru);
                }
            }

            DB::commit();
            return redirect()->route('pesanan.index')->with('success', 'Order berhasil! ' . $invoice);

        } catch (\Exception $e) { // <--- Masalah utama tadi disini (kurung kurawal penutup try hilang)
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }

    // Fungsi tambahan tetap sama...
    public function toggleWa($id, $type) {
        $order = Order::findOrFail($id);
        if ($type == 1) $order->wa_sent_1 = !$order->wa_sent_1;
        elseif ($type == 2) $order->wa_sent_2 = !$order->wa_sent_2;
        $order->save();
        return back();
    }

    public function updateDetail(Request $request, $id)
    {
    public function updateDetail(Request $request, $id) {
        $detail = OrderDetail::findOrFail($id);
        if ($request->has('status')) {
            $detail->status = $request->status;
            $detail->save();
        }
        $order = $detail->order;
        $itemBelumSelesai = $order->details()->whereNotIn('status', ['Selesai', 'Diambil'])->count();
        if ($itemBelumSelesai == 0) $order->status_order = 'Selesai';
        else $order->status_order = 'Proses';
        $order->save();
        return back()->with('success', 'Status berhasil diperbarui');
        $order->status_order = ($itemBelumSelesai == 0) ? 'Selesai' : 'Proses';
        $order->save();
        return back();
    }

    public function checkCustomer(Request $request) {
        $customer = \App\Models\Customer::with('member')->where('no_hp', $request->no_hp)->first();
        if ($customer) {
            $poin = $customer->member ? $customer->member->poin : 0;
            return response()->json([
                'found' => true,
                'nama' => $customer->nama,
                'tipe' => $customer->member ? 'Member' : 'Regular',
                'poin' => $poin,
                'target' => 8, 
                'bisa_claim' => $poin >= 8,
                'member_id' => $customer->member ? $customer->member->id : null,
            ]);
        }
        return response()->json(['found' => false]);
    }
}