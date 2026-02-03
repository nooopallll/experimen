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
        // 1. Cari Member berdasarkan ID
        $member = Member::find($request->member_id);

        if (!$member) {
            // UBAH: Gunakan back()->with('error', ...) bukan response()->json()
            return back()->with('error', 'Member tidak ditemukan.');
        }

        // 2. Tentukan Target Poin (Sesuaikan dengan tampilan Anda, misal 8)
        $targetPoin = 8; 

        // 3. Cek apakah poin cukup
        if ($member->poin < $targetPoin) {
            return back()->with('error', 'Poin belum cukup untuk klaim reward!');
        }

        // 4. Kurangi Poin
        $member->decrement('poin', $targetPoin);

        // (Opsional) 5. Simpan data reward ke pesanan
        // Di sini Anda bisa menambahkan kode untuk mencatat reward yang dipilih ke Order Detail

        // 6. SUKSES: Kembali ke halaman Detail Pesanan
        return back()->with('success', 'Reward "' . $request->reward_item . '" berhasil diklaim! Poin telah dipotong.');
    }
}