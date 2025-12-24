<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Menampilkan halaman Manajemen Pesanan (Daftar Order).
     */
    public function index()
    {
        // Mengambil data urut dari yang terbaru
        $orders = Order::latest()->get();
        return view('manajemen-pesanan', compact('orders'));
    }

    /**
     * Mengecek Nomor HP untuk menentukan status Baru/Repeat.
     */
    public function checkNumber(Request $request)
    {
        $request->validate([
            'no_hp' => 'required',
        ]);

        $no_hp = $request->no_hp;

        // Cek riwayat order
        $history = Order::where('no_hp', $no_hp)->get();
        $count = $history->count();

        // Ambil nama terakhir jika ada
        $lastOrder = $history->last();
        $nama = $lastOrder ? $lastOrder->nama_customer : '';

        // Logika Repeat Order (>= 2 kali)
        if ($count >= 1) {
            return view('orders.input-order', [
                'no_hp' => $no_hp,
                'status' => 'Repeat',
                'color'  => 'text-green-500',
                'nama'   => $nama
            ]);
        } 
        
        // Logika New Order
        return view('orders.input-order', [
            'no_hp' => $no_hp,
            'status' => 'Baru',
            'color'  => 'text-blue-600',
            'nama'   => $nama
        ]);
    }

    /**
     * Menyimpan data order ke database (Support Multiple Items).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'no_hp' => 'required',
            'nama_customer' => 'required',
            // Validasi untuk array (pastikan setiap item terisi)
            'item.*' => 'required', 
            'harga.*' => 'required',
        ]);

        // 2. Ambil data array item
        $items = $request->item;

        // 3. Looping untuk menyimpan setiap item sebagai baris terpisah
        if ($items) {
            foreach ($items as $index => $itemName) {
                // Lewati jika nama item kosong (jaga-jaga)
                if (empty($itemName)) continue;

                Order::create([
                    // Data Header (Sama untuk semua item)
                    'no_hp'          => $request->no_hp,
                    'nama_customer'  => $request->nama_customer,
                    'tipe_customer'  => $request->tipe_customer,
                    'cs'             => $request->cs,
                    'pembayaran'     => $request->pembayaran,
                    'sumber_info'    => $request->sumber_info,
                    'status'         => 'Proses', // Default status

                    // Data Detail (Berbeda tiap baris, ambil berdasarkan index)
                    'item'               => $itemName,
                    'kategori_treatment' => $request->kategori_treatment[$index] ?? null,
                    'tanggal_keluar'     => $request->tanggal_keluar[$index] ?? null,
                    'harga'              => $request->harga[$index] ?? 0,
                    'catatan'            => $request->catatan[$index] ?? '-',
                    'jumlah'             => 1, // Di database dihitung 1 per row
                ]);
            }
        }

        // 4. Redirect kembali ke halaman manajemen pesanan
        return redirect()->route('pesanan.index')->with('success', 'Data berhasil disimpan!');
    }
}