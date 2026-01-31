<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get kategori IDs
        $atk = Kategori::where('nama_kategori', 'Alat Tulis Kantor')->first();
        $makanan = Kategori::where('nama_kategori', 'Makanan & Minuman')->first();
        $rumahTangga = Kategori::where('nama_kategori', 'Rumah Tangga')->first();
        $elektronik = Kategori::where('nama_kategori', 'Elektronik')->first();

        $barangs = [
            // ATK
            [
                'kode_barang' => 'ATK-001',
                'kategori_id' => $atk->id,
                'nama' => 'Kertas A4 70gr (Ream)',
                'harga_beli' => 38000,
                'harga_jual' => 45000,
                'stok' => 50,
            ],
            [
                'kode_barang' => 'ATK-002',
                'kategori_id' => $atk->id,
                'nama' => 'Pulpen Standard (Box)',
                'harga_beli' => 12000,
                'harga_jual' => 15000,
                'stok' => 100,
            ],
            [
                'kode_barang' => 'ATK-003',
                'kategori_id' => $atk->id,
                'nama' => 'Spidol Whiteboard',
                'harga_beli' => 8000,
                'harga_jual' => 10000,
                'stok' => 75,
            ],

            // Makanan & Minuman
            [
                'kode_barang' => 'MKN-001',
                'kategori_id' => $makanan->id,
                'nama' => 'Indomie Goreng (Karton)',
                'harga_beli' => 45000,
                'harga_jual' => 55000,
                'stok' => 30,
            ],
            [
                'kode_barang' => 'MKN-002',
                'kategori_id' => $makanan->id,
                'nama' => 'Aqua 600ml (Karton)',
                'harga_beli' => 32000,
                'harga_jual' => 40000,
                'stok' => 40,
            ],
            [
                'kode_barang' => 'MKN-003',
                'kategori_id' => $makanan->id,
                'nama' => 'Kopi Kapal Api (Box)',
                'harga_beli' => 18000,
                'harga_jual' => 22000,
                'stok' => 60,
            ],

            // Rumah Tangga
            [
                'kode_barang' => 'RT-001',
                'kategori_id' => $rumahTangga->id,
                'nama' => 'Sabun Cuci Piring 800ml',
                'harga_beli' => 15000,
                'harga_jual' => 18000,
                'stok' => 45,
            ],
            [
                'kode_barang' => 'RT-002',
                'kategori_id' => $rumahTangga->id,
                'nama' => 'Sapu Lidi',
                'harga_beli' => 12000,
                'harga_jual' => 15000,
                'stok' => 25,
            ],

            // Elektronik
            [
                'kode_barang' => 'ELK-001',
                'kategori_id' => $elektronik->id,
                'nama' => 'Baterai AA (Pack)',
                'harga_beli' => 18000,
                'harga_jual' => 22000,
                'stok' => 80,
            ],
            [
                'kode_barang' => 'ELK-002',
                'kategori_id' => $elektronik->id,
                'nama' => 'Kabel USB Type-C 1m',
                'harga_beli' => 25000,
                'harga_jual' => 35000,
                'stok' => 50,
            ],

            // Additional seed items
            [
                'kode_barang' => 'ATK-004',
                'kategori_id' => $atk->id,
                'nama' => 'Penghapus (Pack)',
                'harga_beli' => 5000,
                'harga_jual' => 7000,
                'stok' => 200,
            ],
            [
                'kode_barang' => 'MKN-004',
                'kategori_id' => $makanan->id,
                'nama' => 'Gula Pasir 1kg',
                'harga_beli' => 12000,
                'harga_jual' => 15000,
                'stok' => 60,
            ],
            [
                'kode_barang' => 'RT-003',
                'kategori_id' => $rumahTangga->id,
                'nama' => 'Lap Microfiber',
                'harga_beli' => 15000,
                'harga_jual' => 22000,
                'stok' => 40,
            ],
            [
                'kode_barang' => 'ELK-003',
                'kategori_id' => $elektronik->id,
                'nama' => 'Headset Earbuds',
                'harga_beli' => 60000,
                'harga_jual' => 85000,
                'stok' => 30,
            ],
            [
                'kode_barang' => 'ATK-005',
                'kategori_id' => $atk->id,
                'nama' => 'Notes Post-it (Box)',
                'harga_beli' => 20000,
                'harga_jual' => 28000,
                'stok' => 120,
            ],
        ];

        foreach ($barangs as $barang) {
            Barang::firstOrCreate(
                ['kode_barang' => $barang['kode_barang']],
                $barang
            );
        }

        $this->command->info('Barangs seeded successfully!');
    }
}
