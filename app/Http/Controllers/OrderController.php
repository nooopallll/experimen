<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail; // Model Baru
use App\Models\Customer;    // Model Baru
use App\Models\Member;      // Model Baru
use Illuminate\Support\Facades\DB; // Wajib untuk Transaction

class OrderController extends Controller
{
    /**
     * 1. HALAMAN MANAJEMEN PESANAN (INDEX)
     * Menampilkan daftar order dengan Relasi Customer & Details
     */
    public function index(Request $request)
    {
        // Gunakan Eager Loading 'with' agar performa cepat
        $query = Order::with(['customer', 'details'])->latest();

        // LOGIKA SEARCH
        if ($request->filled('search')) {
            $keyword = $request->search;
            
            $query->where(function($q) use ($keyword) {
                $q->where('no_invoice', 'like', "%{$keyword}%") // Cari No Invoice
                  ->orWhereHas('customer', function($c) use ($keyword) {
                      // Cari Nama atau No HP di tabel Customer (Relasi)
                      $c->where('nama', 'like', "%{$keyword}%")
                        ->orWhere('no_hp', 'like', "%{$keyword}%");
                  });
            });
        }

        $orders = $query->paginate(10); // Tidak perlu grouping manual lagi


        return view('pesanan.index', compact('orders')); // Pastikan nama file view sesuai
    }

    /**
     * 2. CEK STATUS CUSTOMER SEBELUM ORDER
     * (New / Repeat / Member)
     */
    public function check(Request $request)
    {
        // Cari customer beserta data member-nya
        $customer = Customer::where('no_hp', $request->no_hp)->with('member')->first();

        // Default variable (Untuk Customer Baru)
        $data = [
            'no_hp' => $request->no_hp,
            'customer' => null,
            'status' => 'New Customer',
            'color' => 'text-blue-500 bg-blue-50 border-blue-200',
            'is_member' => false,
            'poin' => 0
        ];

        if ($customer) {
            $data['customer'] = $customer;
            $data['no_hp'] = $customer->no_hp;

            if ($customer->member) {
                // KONDISI 1: MEMBER
                $data['status'] = 'MEMBER';
                $data['color'] = 'text-pink-600 bg-pink-100 border-pink-200';
                $data['is_member'] = true;
                $data['poin'] = $customer->member->poin;
            } else {
                // KONDISI 2: REPEAT ORDER
                $data['status'] = 'Repeat Order';
                $data['color'] = 'text-green-600 bg-green-100 border-green-200';
                $data['is_member'] = false;
            }
        }

        // Kirim data ke View Input Order
        return view('input-order', $data);
    }

    /**
     * 3. SIMPAN ORDER (CORE FUNCTION)
     * Menyimpan ke 3 Tabel sekaligus (Customers -> Orders -> OrderDetails)
     */
    public function store(Request $request)
{
    // 1. VALIDASI DIHIDUPKAN KEMBALI
    $request->validate([
        'nama_customer' => 'required',
        'no_hp' => 'required',
        'item.*' => 'required',
        'harga.*' => 'required', // Hapus 'numeric' agar tidak error kena "Rp"
    ]);

    try {
        DB::beginTransaction(); // 2. TRANSACTION DIHIDUPKAN

        // ... Simpan Customer (Sama seperti sebelumnya) ...
        $customer = Customer::firstOrCreate(
            ['no_hp' => $request->no_hp],
            ['nama' => $request->nama_customer]
        );

        // ... Siapkan Invoice & Harga (Sama seperti sebelumnya) ...
        $count = Order::whereDate('created_at', today())->count() + 1;
        $invoice = 'INV-' . date('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $totalHarga = 0;
        if(is_array($request->harga)){
             $totalHarga = array_sum(array_map(function($h) {
                 return (int) preg_replace('/[^0-9]/', '', $h); 
             }, $request->harga));
        }

        // 3. Simpan Order (LENGKAP DENGAN TGL_MASUK)
        $order = Order::create([
            'no_invoice' => $invoice,
            'customer_id' => $customer->id,
            'tgl_masuk' => now(), // <--- INI KUNCI SUKSESNYA
            'total_harga' => $totalHarga,
            'status_pembayaran' => $request->pembayaran,
            'status_order' => 'Proses',
            'tipe_customer' => $request->tipe_customer,
            'sumber_info' => $request->sumber_info,
            'catatan' => $request->catatan[0] ?? '-',
            'kasir' => $request->cs ?? 'Admin',
        ]);

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
                    'status' => 'Proses',
                ]);
            }
        }

        // ... Update Member (Sama seperti sebelumnya) ...
        if ($customer->member) {
            $customer->member->increment('total_transaksi', $totalHarga);
            $poinBaru = floor($totalHarga / 50000);
            $customer->member->increment('poin', $poinBaru);
        }

        DB::commit(); // 4. COMMIT DIHIDUPKAN

        return redirect()->route('pesanan.index')->with('success', 'Order berhasil diinput!');

    } catch (\Exception $e) {
        DB::rollBack(); // 5. ROLLBACK DIHIDUPKAN
        // Tampilkan error jika gagal
        return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
    }
}

public function toggleWa($id, $type)
    {
        $order = Order::findOrFail($id);
        
        if ($type == 1) {
            $order->wa_sent_1 = !$order->wa_sent_1; // Ubah true ke false, atau sebaliknya
        } elseif ($type == 2) {
            $order->wa_sent_2 = !$order->wa_sent_2;
        }
        
        $order->save();
        
        // Kembali ke halaman sebelumnya tanpa pesan flash (agar cepat)
        return back();
    }

    public function updateDetail(Request $request, $id)
{
    // 1. Update Status Item yang Dipilih
    $detail = OrderDetail::findOrFail($id);
    
    if ($request->has('status')) {
        $detail->status = $request->status;
        $detail->save();
    }

    // 2. LOGIKA OTOMATIS: Cek Status Order Utama (Parent)
    $order = $detail->order;
    
    // Hitung berapa item yang statusnya BUKAN 'Selesai' atau 'Diambil'
    $itemBelumSelesai = $order->details()
        ->whereNotIn('status', ['Selesai', 'Diambil'])
        ->count();

    if ($itemBelumSelesai == 0) {
        // Jika 0 (artinya semua sudah selesai/diambil), ubah Order Utama jadi Selesai
        $order->status_order = 'Selesai';
    } else {
        // Jika masih ada yang belum selesai, paksa Order Utama jadi Proses
        $order->status_order = 'Proses';
    }
    
    $order->save();

    return back()->with('success', 'Status berhasil diperbarui');
}

public function checkCustomer(Request $request)
{
    $customer = \App\Models\Customer::with('member')
                ->where('no_hp', $request->no_hp)
                ->first();

    if ($customer) {
        $poin = $customer->member ? $customer->member->poin : 0;
        $targetPoin = 8; // Contoh: Target 10 poin untuk klaim
        
        return response()->json([
            'found' => true,
            'nama' => $customer->nama,
            'tipe' => $customer->member ? 'Member' : 'Regular',
            'poin' => $poin,
            'target' => $targetPoin, 
            'bisa_claim' => $poin >= $targetPoin, // True jika poin cukup
            'member_id' => $customer->member ? $customer->member->id : null,
        ]);
    }

    return response()->json(['found' => false]);
}

}