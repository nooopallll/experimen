<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::latest()->get();
        return view('owner.karyawan', compact('karyawans'));
    }

    public function store(Request $request)
    {
        $request->validate(['nama_karyawan' => 'required|string|max:255']);
        Karyawan::create($request->all());
        return redirect()->back()->with('success', 'Karyawan berhasil ditambah!');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['nama_karyawan' => 'required|string|max:255']);
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->update($request->all());
        return redirect()->back()->with('success', 'Nama karyawan berhasil diubah!');
    }

    public function destroy($id)
    {
        Karyawan::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Karyawan berhasil dihapus!');
    }
}