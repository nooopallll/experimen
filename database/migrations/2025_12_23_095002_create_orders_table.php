<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_create_orders_table.php
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice')->unique(); // Contoh: INV-2023001
            $table->foreignId('customer_id')->constrained('customers'); // Hubungkan ke customer
            
            $table->dateTime('tgl_masuk');
            $table->dateTime('estimasi_selesai')->nullable();
            $table->decimal('total_harga', 12, 2)->default(0); // Total akhir
            $table->string('status_pembayaran')->default('Belum Lunas');
            $table->string('status_order')->default('Baru'); // Baru, Proses, Selesai
            $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
