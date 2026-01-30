<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator; // <--- PENTING: Tambahkan ini

class MemberController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Manual (Agar tidak auto-redirect 302)
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'no_hp' => 'required',
        ]);

        // Jika validasi gagal, kirim JSON Error (jangan redirect!)
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal: ' . $validator->errors()->first()
            ], 422); // 422 = Unprocessable Entity
        }

        try {
            DB::beginTransaction();

            // 2. Cari atau Buat Customer
            $customer = Customer::firstOrCreate(
                ['no_hp' => $request->no_hp],
                ['nama' => $request->nama, 'alamat' => $request->alamat]
            );

            // 3. Cek apakah sudah member
            if ($customer->member) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nomor HP ini sudah terdaftar sebagai Member.'
                ], 400); // 400 = Bad Request
            }

            // 4. Simpan Member
            Member::create([
                'customer_id' => $customer->id,
                'level' => 'Silver',
                'poin' => $request->initial_poin ?? 0,
                'total_transaksi' => $request->initial_total ?? 0
            ]);

            DB::commit();

            // 5. SUKSES: Kirim JSON (Bukan redirect/back)
            return response()->json([
                'status' => 'success',
                'message' => 'Member berhasil didaftarkan!',
                'poin' => $request->initial_poin
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // 6. ERROR SYSTEM: Kirim JSON
            return response()->json([
                'status' => 'error',
                'message' => 'Error Server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function claimPoints(Request $request)
    {
        try {
            DB::beginTransaction();

            $member = Member::find($request->member_id);
            $order = Order::find($request->order_id);

            if (!$member || !$order) return back()->with('error', 'Data error.');
            if ($member->poin < 8) return back()->with('error', 'Poin tidak cukup.');

            // Potong Poin
            $member->decrement('poin', 8);

            $rewardName = $request->reward_item; // 'diskon' atau 'Parfum', dll

            // Cek Jenis Reward
            if ($rewardName === 'diskon') {
                // --- KASUS DISKON ---
                $potongan = 35000;
                $order->discount = $potongan;
                $order->total_harga = max(0, $order->total_harga - $potongan);
                $order->save();
            } else {
                // --- KASUS BARANG (PARFUM/DLL) ---
                OrderDetail::create([
                    'order_id' => $order->id,
                    'nama_barang' => $rewardName, // Nama Barang
                    'layanan' => 'Member Reward',
                    'harga' => 0, // Gratis
                    'estimasi_keluar' => now(),
                    'status' => 'Diambil',
                    'klaim' => $rewardName // Isi kolom klaim
                ]);
            }

            DB::commit();
            return back()->with('success', "Reward berhasil diklaim!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}