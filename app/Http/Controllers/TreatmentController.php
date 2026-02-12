<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{
    public function index()
    {
        $treatments = Treatment::orderBy('kategori', 'asc')->get();
    // Dari owner.treatments.index menjadi owner.treatments
    return view('owner.treatments', compact('treatments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'required',
            'nama_treatment' => 'required',
        ]);

        Treatment::create($request->all());
        return back()->with('success', 'Treatment berhasil ditambah!');
    }

    public function update(Request $request, $id)
    {
        $treatment = Treatment::findOrFail($id);
        $treatment->update($request->all());
        return back()->with('success', 'Treatment berhasil diperbarui!');
    }

    public function destroy($id)
    {
        Treatment::destroy($id);
        return back()->with('success', 'Treatment berhasil dihapus!');
    }
}