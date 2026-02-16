<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderDetail;
use App\Models\Order;

class OrderDetailController extends Controller
{
    public function updateStatus(Request $request, $id)
    {
        $detail = OrderDetail::findOrFail($id);
        $detail->update(['status' => $request->status]);

        // PERBAIKAN: Cek ulang seluruh item untuk menentukan status Order Utama
        $order = $detail->order;
        
        $totalDetails = $order->details()->count();
        $unfinishedItems = $order->details()->whereNotIn('status', ['Selesai', 'Diambil'])->count();
        $pickedUpItems = $order->details()->where('status', 'Diambil')->count();

        if ($totalDetails > 0) {
            if ($unfinishedItems > 0) {
                // Jika masih ada 1 saja item yang 'Proses', Order Utama TETAP 'Proses'
                $order->status_order = 'Proses';
            } elseif ($pickedUpItems == $totalDetails) {
                $order->status_order = 'Diambil';
            } else {
                // Jika semua item 'Selesai' (atau kombinasi Selesai & Diambil)
                $order->status_order = 'Selesai';
            }
            $order->save();
        }

        return back()->with('success', 'Status item berhasil diperbarui!');
    }
}