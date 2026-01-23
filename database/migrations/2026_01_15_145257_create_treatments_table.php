<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('treatments', function (Blueprint $table) {
        $table->id();
        $table->string('nama_treatment');
        $table->integer('harga'); // Menggunakan integer untuk harga
        $table->timestamps();
    });
}
};
