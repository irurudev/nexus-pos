<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pelanggans = [
            [
                'id_pelanggan' => 'PEL-001',
                'nama' => 'PT Maju Jaya Sentosa',
                'domisili' => 'Jakarta Pusat',
                'jenis_kelamin' => 'PRIA',
                'poin' => 0,
            ],
            [
                'id_pelanggan' => 'PEL-002',
                'nama' => 'CV Berkah Mandiri',
                'domisili' => 'Tangerang',
                'jenis_kelamin' => 'WANITA',
                'poin' => 0,
            ],
            [
                'id_pelanggan' => 'PEL-003',
                'nama' => 'Toko Sumber Rezeki',
                'domisili' => 'Jakarta Selatan',
                'jenis_kelamin' => 'PRIA',
                'poin' => 0,
            ],
            [
                'id_pelanggan' => 'PEL-004',
                'nama' => 'UD Cahaya Abadi',
                'domisili' => 'Bekasi',
                'jenis_kelamin' => 'PRIA',
                'poin' => 0,
            ],
            [
                'id_pelanggan' => 'PEL-005',
                'nama' => 'Toko Serba Ada',
                'domisili' => 'Depok',
                'jenis_kelamin' => 'WANITA',
                'poin' => 0,
            ],
        ];

        foreach ($pelanggans as $pelanggan) {
            Pelanggan::firstOrCreate(
                ['id_pelanggan' => $pelanggan['id_pelanggan']],
                $pelanggan
            );
        }

        $this->command->info('Pelanggans seeded successfully!');
    }
}
