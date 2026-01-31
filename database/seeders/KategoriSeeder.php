<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoris = [
            ['nama_kategori' => 'Alat Tulis Kantor'],
            ['nama_kategori' => 'Makanan & Minuman'],
            ['nama_kategori' => 'Rumah Tangga'],
            ['nama_kategori' => 'Elektronik'],
            ['nama_kategori' => 'Kesehatan'],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::firstOrCreate(
                ['nama_kategori' => $kategori['nama_kategori']],
                $kategori
            );
        }

        $this->command->info('Kategoris seeded successfully!');
    }
}
