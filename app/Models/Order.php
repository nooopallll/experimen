<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model ini.
     */
    protected $table = 'orders';

    /**
     * Atribut yang dapat diisi secara massal.
     * Sesuaikan dengan kolom yang Anda buat di Migration.
     */
    protected $fillable = [
        'no_hp',
        'nama_customer',
        'jumlah',
        'cs',
        'item',
        'kategori_treatment',
        'tanggal_keluar',
        'harga',
        'catatan',
        'pembayaran',
        'tipe_customer', // Untuk menyimpan status 'Baru' atau 'Repeat'
        'sumber_info'
    ];

    /**
     * Casting tipe data jika diperlukan.
     */
    protected $casts = [
        'tanggal_keluar' => 'date',
        'harga' => 'integer',
        'jumlah' => 'integer',
    ];
}