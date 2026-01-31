<?php

use App\Models\Barang;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('allows creating penjualan without id_nota and tgl (backend generates them)', function () {
    $user = User::factory()->create(['role' => 'kasir']);

    $kategori = DB::table('kategoris')->insertGetId(['nama_kategori' => 'Test-'.uniqid(), 'created_at' => now(), 'updated_at' => now()]);

    $barang = Barang::create([
        'kode_barang' => Barang::generateKode(),
        'kategori_id' => $kategori,
        'nama' => 'Item A',
        'harga_beli' => 5000,
        'harga_jual' => 10000,
        'stok' => 10,
    ]);

    $payload = [
        'items' => [
            ['kode_barang' => $barang->kode_barang, 'qty' => 2, 'harga_satuan' => 10000],
        ],
    ];

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/penjualans', $payload);

    $response->assertStatus(201);

    $json = $response->json();

    // expect id_nota generated and tgl set
    expect(isset($json['data']['id_nota']))->toBeTrue();
    expect(isset($json['data']['tgl']))->toBeTrue();

    // expect stock decreased
    $barang->refresh();
    expect($barang->stok)->toBe(8);
});