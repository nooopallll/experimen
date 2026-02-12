<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
    'nama', 
    'no_hp', 
    'tipe',       // <--- Gunakan 'tipe' sesuai database Anda
    'alamat',     // (Opsional, jika mau diisi)
    'sumber_info' // <--- Kolom baru
];
    // Relasi: Customer mungkin punya 1 data Member
    public function member()
    {
        return $this->hasOne(Member::class);
    }

    // Relasi: Customer punya BANYAK Order
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}