<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'nama_barang',
        'layanan',
        'harga',
        'estimasi_keluar', // <--- TAMBAHKAN INI (WAJIB)
        'tanggal_keluar', // <--- WAJIB DITAMBAHKAN
        'estimasi_keluar', // <--- WAJIB DITAMBAHKAN
        'catatan',        // <--- WAJIB DITAMBAHKAN
        'status',
        'klaim', // <--- TAMBAHKAN INI
    ];

    // Relasi balik ke Order Utama
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}