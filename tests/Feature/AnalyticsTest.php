<?php

use Illuminate\Support\Facades\DB;
use App\Models\Barang;

it('returns correct summary analytics for given period', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);

    $kategoriId = DB::table('kategoris')->insertGetId(['nama_kategori' => 'Food', 'created_at' => now(), 'updated_at' => now()]);

    $kode = Barang::generateKode();
    DB::table('barangs')->insert([
        'kode_barang' => $kode,
        'kategori_id' => $kategoriId,
        'nama' => 'Test Item',
        'harga_beli' => 1000,
        'harga_jual' => 1500,
        'stok' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $nota = 'INV-ANALYTICS-1';
    DB::table('penjualans')->insert([
        'id_nota' => $nota,
        'tgl' => '2025-01-15 12:00:00',
        'user_id' => $admin->id,
        'total_akhir' => 3000,
        'diskon' => 0,
        'pajak' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('item_penjualans')->insert([
        'nota' => $nota,
        'kode_barang' => $kode,
        'qty' => 2,
        'harga_satuan' => 1500,
        'jumlah' => 3000,
        'nama_barang' => 'Test Item',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $res = $this->actingAs($admin, 'sanctum')->getJson('/api/analytics/summary?start_date=2025-01-01&end_date=2025-01-31');

    $res->assertStatus(200);
    $data = $res->json('data');

    // total_penjualan should be 3000, laba = (1500-1000)*2 = 1000
    expect((float) $data['total_penjualan'])->toBe(3000.0);
    expect((float) $data['total_laba'])->toBe(1000.0);
    expect((int) $data['jumlah_transaksi'])->toBe(1);
});

it('returns top kategori ordered by qty', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);

    $k1 = DB::table('kategoris')->insertGetId(['nama_kategori' => 'A', 'created_at' => now(), 'updated_at' => now()]);
    $k2 = DB::table('kategoris')->insertGetId(['nama_kategori' => 'B', 'created_at' => now(), 'updated_at' => now()]);

    $kode1 = Barang::generateKode();
    DB::table('barangs')->insert(['kode_barang' => $kode1, 'kategori_id' => $k1, 'nama' => 'I1', 'harga_beli' => 100, 'harga_jual' => 200, 'stok' => 10, 'created_at' => now(), 'updated_at' => now()]);

    $kode2 = Barang::generateKode();
    DB::table('barangs')->insert(['kode_barang' => $kode2, 'kategori_id' => $k2, 'nama' => 'I2', 'harga_beli' => 100, 'harga_jual' => 200, 'stok' => 10, 'created_at' => now(), 'updated_at' => now()]);

    $nota1 = 'N1';
    DB::table('penjualans')->insert(['id_nota' => $nota1, 'tgl' => now()->toDateTimeString(), 'user_id' => $admin->id, 'total_akhir' => 2000, 'diskon' => 0, 'pajak' => 0, 'created_at' => now(), 'updated_at' => now()]);

    DB::table('item_penjualans')->insert(['nota' => $nota1, 'kode_barang' => $kode1, 'qty' => 5, 'harga_satuan' => 200, 'jumlah' => 1000, 'nama_barang' => 'I1', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('item_penjualans')->insert(['nota' => $nota1, 'kode_barang' => $kode2, 'qty' => 2, 'harga_satuan' => 200, 'jumlah' => 400, 'nama_barang' => 'I2', 'created_at' => now(), 'updated_at' => now()]);

    $res = $this->actingAs($admin, 'sanctum')->getJson('/api/analytics/top-kategori?start_date='.now()->startOfMonth()->toDateString().'&end_date='.now()->toDateString());
    $res->assertStatus(200);

    $items = $res->json('data.items');
    expect((int) $items[0]['total_qty'])->toBe(5);
});

it('returns kasir performance aggregated by month/year', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);
    $kasir = \App\Models\User::factory()->create(['role' => 'kasir']);

    DB::table('penjualans')->insert(['id_nota' => 'P1', 'tgl' => '2024-02-10 12:00:00', 'user_id' => $kasir->id, 'total_akhir' => 1000, 'diskon' => 0, 'pajak' => 0, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('penjualans')->insert(['id_nota' => 'P2', 'tgl' => '2024-02-20 12:00:00', 'user_id' => $kasir->id, 'total_akhir' => 2000, 'diskon' => 0, 'pajak' => 0, 'created_at' => now(), 'updated_at' => now()]);

    $res = $this->actingAs($admin, 'sanctum')->getJson('/api/analytics/kasir-performance?year=2024');
    $res->assertStatus(200);

    $items = $res->json('data.items');
    // find kasir rows for bulan 2
    $found = false;
    foreach ($items as $r) {
        if ($r['id'] === $kasir->id && $r['bulan'] == 2) {
            $found = true;
            expect((float) $r['total_penjualan'])->toBe(3000.0);
            expect((int) $r['jumlah_transaksi'])->toBe(2);
        }
    }

    expect($found)->toBeTrue();
});
