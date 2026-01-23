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
    Schema::table('order_details', function (Blueprint $table) {
        // Menambahkan kolom tanggal setelah kolom harga
        $table->date('estimasi_keluar')->nullable()->after('harga');
    });
}

public function down()
{
    Schema::table('order_details', function (Blueprint $table) {
        $table->dropColumn('estimasi_keluar');
    });
}
};
