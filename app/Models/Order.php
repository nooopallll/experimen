<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_invoice',
        'customer_id',
        'tgl_masuk',
        'estimasi_selesai',
        'total_harga',
        'discount',
        'paid_amount',        
        'status_pembayaran',
        'metode_pembayaran',  
        'status_order',
        'tipe_customer',
        'sumber_info',
        'catatan',
        'kasir',
        'kasir_keluar', // <--- WAJIB DITAMBAHKAN DISINI
        'wa_sent_1',
        'wa_sent_2',
    ];

    // Relasi ke Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relasi ke Order Details
    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }
}