<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getDiskonMember()
    {
        return (int) (self::where('key', 'diskon_member')->value('value') ?? 10000);
    }
}