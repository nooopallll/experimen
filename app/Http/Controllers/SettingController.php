<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'owner') {
            return redirect()->route('dashboard');
        }

        $diskon = Setting::getDiskonMember();
        return view('owner.settings', compact('diskon'));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role !== 'owner') {
            return abort(403);
        }

        $request->validate([
            'diskon_member' => 'required|numeric|min:0'
        ]);

        Setting::updateOrCreate(
            ['key' => 'diskon_member'],
            ['value' => $request->diskon_member]
        );

        return back()->with('success', 'Nominal diskon member berhasil diperbarui!');
    }
}