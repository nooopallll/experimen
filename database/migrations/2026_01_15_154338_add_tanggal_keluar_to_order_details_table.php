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
        Schema::table('order_details', function (Blueprint $table) {
            // Tambahkan kolom tanggal_keluar jika belum ada
            if (!Schema::hasColumn('order_details', 'tanggal_keluar')) {
                $table->date('tanggal_keluar')->nullable()->after('harga');
            }
            // Tambahkan kolom catatan jika belum ada (jaga-jaga error berikutnya)
            if (!Schema::hasColumn('order_details', 'catatan')) {
                $table->text('catatan')->nullable()->after('tanggal_keluar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['tanggal_keluar', 'catatan']);
        });
    }
};