<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill nama_barang from current barangs table (including soft-deleted)
        // Use a platform-specific query; skip on sqlite (used in tests) to avoid SQL syntax errors.
        $driver = DB::getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement(
                "UPDATE item_penjualans ip JOIN barangs b ON ip.kode_barang = b.kode_barang SET ip.nama_barang = b.nama WHERE ip.nama_barang IS NULL"
            );
        }
    }

    public function down(): void
    {
        // no-op (don't remove existing historical data)
    }
};