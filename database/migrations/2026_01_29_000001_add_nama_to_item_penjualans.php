<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_penjualans', function (Blueprint $table) {
            $table->string('nama_barang')->nullable()->after('kode_barang');
        });
    }

    public function down(): void
    {
        Schema::table('item_penjualans', function (Blueprint $table) {
            $table->dropColumn('nama_barang');
        });
    }
};