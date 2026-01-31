<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SequenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Initialize sequences based on existing data (set to current max suffix if exists)

        // Barang: look for codes starting with BRG and numeric suffix
        $maxBarang = DB::table('barangs')
            ->where('kode_barang', 'like', 'BRG%')
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(kode_barang, 4) AS UNSIGNED)), 0) as max_suffix')
            ->value('max_suffix');

        DB::table('sequences')->updateOrInsert(
            ['name' => 'barang'],
            ['value' => (int) $maxBarang, 'updated_at' => now(), 'created_at' => now()]
        );

        // Pelanggan: look for codes starting with PGN
        $maxPelanggan = DB::table('pelanggans')
            ->where('id_pelanggan', 'like', 'PGN%')
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(id_pelanggan, 4) AS UNSIGNED)), 0) as max_suffix')
            ->value('max_suffix');

        DB::table('sequences')->updateOrInsert(
            ['name' => 'pelanggan'],
            ['value' => (int) $maxPelanggan, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
