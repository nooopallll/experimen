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
    Schema::table('customers', function (Blueprint $table) {
        // Kita tambahkan sumber_info saja
        $table->string('sumber_info')->nullable()->after('alamat'); 
    });
}

public function down()
{
    Schema::table('customers', function (Blueprint $table) {
        $table->dropColumn('sumber_info');
    });
}
};
