<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    protected $fillable = ['kategori', 'nama_treatment', 'harga'];
}