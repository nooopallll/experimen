<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Data Utama (Penting untuk pencarian)
            $table->string('no_hp')->index(); // Diberi index agar pencarian cepat
            $table->string('nama_customer');
            $table->string('tipe_customer')->default('Baru'); // Baru atau Repeat

            // Detail Order
            $table->integer('jumlah')->default(1);
            $table->string('cs')->nullable(); // Nama Admin/CS
            $table->string('item')->nullable(); // Nama Sepatu/Barang
            $table->string('kategori_treatment')->nullable(); // Deep Clean, Fast Clean, dll
            
            // Keuangan & Tanggal
            $table->date('tanggal_keluar')->nullable();
            $table->bigInteger('harga')->default(0); // Gunakan bigInteger untuk harga rupiah
            $table->string('pembayaran')->nullable(); // Transfer/Cash/Qris
            
            // Tambahan
            $table->text('catatan')->nullable();
            $table->string('sumber_info')->nullable(); // Tau dari mana (IG, Maps, dll)
            $table->string('status')->default('Proses'); // Status default pesanan
            
            $table->timestamps(); // Created_at & Updated_at
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