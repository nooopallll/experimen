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
                $data['color'] = 'text-green-600 bg-green-100 border-green-200';
                $data['is_member'] = false;
            }
        }

        return view('input-order', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_customer' => 'required',
            'no_hp' => 'required',
            'item.*' => 'required',
            'harga.*' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::firstOrCreate(
                ['no_hp' => $request->no_hp],
                ['nama' => $request->nama_customer]
            );

            $count = Order::whereDate('created_at', today())->count() + 1;
            $invoice = 'INV-' . date('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

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

                OrderDetail::create([
                    'order_id' => $order->id,
                    'nama_barang' => $items[$i],
                    'layanan' => $request->kategori_treatment[$i] ?? 'General',
                    'harga' => $hargaBersih,
                    'status' => 'Proses',
                ]);
            }
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
        $order = Order::findOrFail($id);
        if ($type == 1) $order->wa_sent_1 = !$order->wa_sent_1;
        elseif ($type == 2) $order->wa_sent_2 = !$order->wa_sent_2;
        $order->save();
        return back();
    }

    public function updateDetail(Request $request, $id)
    {
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
    }
}