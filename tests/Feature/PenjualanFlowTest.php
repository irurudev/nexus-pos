<?php

use App\Actions\Penjualan\CreatePenjualanAction;
use App\DTOs\ItemPenjualanData;
use App\DTOs\PenjualanData;
use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('creates penjualan and decrements stok accordingly', function () {
    // create user (kasir)
    $user = User::factory()->create(['role' => 'kasir']);

    // create barang with stok 10
    $kategori = DB::table('kategoris')->insertGetId(['nama_kategori' => 'ATK-'.uniqid(), 'created_at' => now(), 'updated_at' => now()]);

    $barang = Barang::create([
        'kode_barang' => Barang::generateKode(),
        'kategori_id' => $kategori,
        'nama' => 'Test Item',
        'harga_beli' => 10000,
        'harga_jual' => 15000,
        'stok' => 10,
    ]);

    $pelanggan = Pelanggan::create([
        'id_pelanggan' => Pelanggan::generateId(),
        'nama' => 'Test Pel',
        'domisili' => 'Test City',
        'jenis_kelamin' => 'PRIA',
    ]);

    $items = [
        ItemPenjualanData::from([
            'kode_barang' => $barang->kode_barang,
            'qty' => 2,
            'harga_satuan' => 15000,
            'jumlah' => 30000,
        ]),
    ];

    $penjualanData = new PenjualanData(
        id_nota: 'INV-TEST-001',
        tgl: now()->toDateTimeString(),
        kode_pelanggan: $pelanggan->id_pelanggan,
        user_id: $user->id,
        subtotal: 30000,
        diskon: 0,
        pajak: 0,
        items: $items,
    );

    $action = new CreatePenjualanAction;
    $penjualan = $action->execute($penjualanData);

    expect($penjualan)->not->toBeNull();
    expect($penjualan->itemPenjualans)->toHaveCount(1);

    $barang->refresh();
    expect($barang->stok)->toBe(8);
});

it('returns validation error when qty exceeds stock', function () {
    $user = User::factory()->create(['role' => 'kasir']);

    $kategori = DB::table('kategoris')->insertGetId(['nama_kategori' => 'Test-'.uniqid(), 'created_at' => now(), 'updated_at' => now()]);

    $barang = Barang::create([
        'kode_barang' => Barang::generateKode(),
        'kategori_id' => $kategori,
        'nama' => 'Limited Item',
        'harga_beli' => 1000,
        'harga_jual' => 5000,
        'stok' => 1,
    ]);

    $payload = [
        'items' => [
            ['kode_barang' => $barang->kode_barang, 'qty' => 2, 'harga_satuan' => 5000],
        ],
    ];

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/penjualans', $payload);

    $response->assertStatus(422);
    $json = $response->json();
    expect($json['errors']['items.0.qty'][0] ?? '')->toContain('Stok tidak mencukupi');
});

it('retains item nama when barang is soft deleted', function () {
    $user = User::factory()->create(['role' => 'kasir']);

    $kategori = DB::table('kategoris')->insertGetId(['nama_kategori' => 'Test-'.uniqid(), 'created_at' => now(), 'updated_at' => now()]);

    $barang = Barang::create([
        'kode_barang' => Barang::generateKode(),
        'kategori_id' => $kategori,
        'nama' => 'Kopi Mantap',
        'harga_beli' => 1000,
        'harga_jual' => 5000,
        'stok' => 10,
    ]);

    $items = [
        ItemPenjualanData::from([
            'kode_barang' => $barang->kode_barang,
            'qty' => 1,
            'harga_satuan' => 5000,
            'jumlah' => 5000,
        ]),
    ];

    $penjualanData = new PenjualanData(
        id_nota: 'INV-TEST-DEL-001',
        tgl: now()->toDateTimeString(),
        kode_pelanggan: null,
        user_id: $user->id,
        subtotal: 5000,
        diskon: 0,
        pajak: 0,
        items: $items,
    );

    $action = new CreatePenjualanAction;
    $penjualan = $action->execute($penjualanData);

    // soft delete the barang
    $barang->delete();

    // fetch penjualan detail via controller
    $response = $this->actingAs($user, 'sanctum')->getJson('/api/penjualans/'.$penjualan->id_nota);

    $response->assertStatus(200);
    $json = $response->json();

    // the response should contain nama_barang or barang.name despite soft delete
    $item = $json['data']['item_penjualans'][0] ?? $json['data']['itemPenjualans'][0] ?? null;
    expect($item)->not->toBeNull();
    expect($item['kode_barang'])->toBe($barang->kode_barang);
    expect($item['nama_barang'] ?? $item['barang']['nama'])->toBeString();
});
