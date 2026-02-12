<?php

namespace App\Http\Controllers;

use App\Models\Kebutuhan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class KebutuhanController extends Controller
{
    /**
     * Tampilkan halaman utama (List untuk Admin & Owner)
     */
    public function index()
    {
        $data = Kebutuhan::orderBy('created_at', 'desc')->get();

        $formattedData = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama_kebutuhan,
                'stok' => $item->stok_terakhir,
                // PERBAIKAN DI SINI: Gunakan format yang diinginkan JS dan akhiri dengan koma
                'tanggal' => Carbon::parse($item->tanggal)->format('d/m/Y'), 
            ];
        });

        return view('kebutuhan', ['kebutuhans' => $formattedData]);
    }

    /**
     * Tampilkan halaman khusus Owner (Hanya Tabel)
     */
    public function ownerIndex()
    {
        if (auth()->user()->role !== 'owner') {
            return redirect()->route('dashboard');
        }

        // Ambil data dan format agar sesuai dengan view owner
        $kebutuhans = Kebutuhan::latest()->get();

        return view('owner.kebutuhan', compact('kebutuhans'));
    }

    /**
     * Simpan Data via AJAX (Untuk Admin)
     */
    public function store(Request $request)
    {
        // Validasi
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'stok' => 'required|string|max:255',
            'tanggal' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            // Ubah format tanggal dari DD/MM/YYYY ke YYYY-MM-DD untuk database
            $tanggalDb = Carbon::createFromFormat('d/m/Y', $request->tanggal)->format('Y-m-d');

            $kebutuhan = Kebutuhan::updateOrCreate(
                ['id' => $request->id],
                [
                    'nama_kebutuhan' => $request->nama,
                    'stok_terakhir'  => $request->stok,
                    'tanggal'        => $tanggalDb,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'data' => $kebutuhan
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Aksi Owner: Centang = Sudah Dibeli = Hapus Permanen
     */
    public function markAsPurchased($id)
    {
        try {
            $item = Kebutuhan::findOrFail($id);
            $item->delete(); 

            return response()->json([
                'status' => 'success',
                'message' => 'Barang berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus'], 500);
        }
    }

    /**
     * Aksi Admin: Hapus Manual
     */
    public function destroy($id)
    {
        $item = Kebutuhan::findOrFail($id);
        $item->delete();

        return redirect()->route('kebutuhan.index')->with('success', 'Data berhasil dihapus.');
    }
}