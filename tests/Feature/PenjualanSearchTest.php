<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('filters penjualans by search query (id_nota, pelanggan name, or item name)', function () {
    $user = User::factory()->create(['role' => 'kasir']);

    $nota1 = 'TEST-SEARCH-1';
    $nota2 = 'TEST-OTHER-1';

    // insert penjualans
    DB::table('penjualans')->insert([
        'id_nota' => $nota1,
        'tgl' => now()->toDateTimeString(),
        'user_id' => $user->id,
        'subtotal' => 100,
        'diskon' => 0,
        'pajak' => 0,
        'total_akhir' => 100,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('penjualans')->insert([
        'id_nota' => $nota2,
        'tgl' => now()->toDateTimeString(),
        'user_id' => $user->id,
        'subtotal' => 200,
        'diskon' => 0,
        'pajak' => 0,
        'total_akhir' => 200,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // ensure a barang exists for FK constraint
    $kategoriId = DB::table('kategoris')->insertGetId(['nama_kategori' => 'Test-'.uniqid(), 'created_at' => now(), 'updated_at' => now()]);

    DB::table('barangs')->insert([
        'kode_barang' => 'BRG_TEST',
        'kategori_id' => $kategoriId,
        'nama' => 'Test Barang',
        'harga_beli' => 50,
        'harga_jual' => 100,
        'stok' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // add an item for nota1 with distinctive nama_barang
    DB::table('item_penjualans')->insert([
        'nota' => $nota1,
        'kode_barang' => 'BRG_TEST',
        'qty' => 1,
        'harga_satuan' => 100,
        'jumlah' => 100,
        'nama_barang' => 'SEARCHITEM',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // search by item name
    $response = $this->actingAs($user, 'sanctum')->getJson('/api/penjualans?search=SEARCHITEM');

    $response->assertStatus(200);
    $json = $response->json();

    // expect data contains nota1 and not nota2
    $ids = array_map(fn($row) => $row['id_nota'], $json['data']);

    expect(in_array($nota1, $ids))->toBeTrue();
    expect(in_array($nota2, $ids))->toBeFalse();
});
