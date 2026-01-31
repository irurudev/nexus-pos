<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('allows admin to create barang via API', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $kategoriId = DB::table('kategoris')->insertGetId(['nama_kategori' => 'Makanan', 'created_at' => now(), 'updated_at' => now()]);

    $payload = [
        'kategori_id' => $kategoriId,
        'nama' => 'Beras',
        'harga_beli' => 50000,
        'harga_jual' => 65000,
        'stok' => 20,
    ];

    $response = $this->actingAs($admin, 'sanctum')->postJson('/api/barangs', $payload);

    $response->assertStatus(201);
    $this->assertDatabaseHas('barangs', ['nama' => 'Beras']);
});

it('allows admin to update barang via API', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $kategoriId = DB::table('kategoris')->insertGetId(['nama_kategori' => 'Elektronik', 'created_at' => now(), 'updated_at' => now()]);

    $kode = 'BRG999';
    DB::table('barangs')->insert([
        'kode_barang' => $kode,
        'kategori_id' => $kategoriId,
        'nama' => 'Lampu',
        'harga_beli' => 10000,
        'harga_jual' => 15000,
        'stok' => 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin, 'sanctum')->putJson('/api/barangs/'.$kode, ['nama' => 'Lampu LED']);

    $response->assertStatus(200);
    $this->assertDatabaseHas('barangs', ['kode_barang' => $kode, 'nama' => 'Lampu LED']);
});

it('prevents non-admin from updating barang', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);

    $kategoriId = DB::table('kategoris')->insertGetId(['nama_kategori' => 'Elektronik-'.uniqid(), 'created_at' => now(), 'updated_at' => now()]);

    $kode = 'BRG998';
    DB::table('barangs')->insert([
        'kode_barang' => $kode,
        'kategori_id' => $kategoriId,
        'nama' => 'Radio',
        'harga_beli' => 20000,
        'harga_jual' => 25000,
        'stok' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($kasir, 'sanctum')->putJson('/api/barangs/'.$kode, ['nama' => 'Radio Baru']);

    $response->assertStatus(403);
});

it('allows admin to delete barang via API', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $kategoriId = DB::table('kategoris')->insertGetId(['nama_kategori' => 'Hobi', 'created_at' => now(), 'updated_at' => now()]);

    $kode = 'BRG997';
    DB::table('barangs')->insert([
        'kode_barang' => $kode,
        'kategori_id' => $kategoriId,
        'nama' => 'Drone',
        'harga_beli' => 500000,
        'harga_jual' => 600000,
        'stok' => 2,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin, 'sanctum')->deleteJson('/api/barangs/'.$kode);

    $response->assertStatus(200);
    $this->assertSoftDeleted('barangs', ['kode_barang' => $kode]);
});

it('prevents non-admin from deleting barang', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);

    $kategoriId = DB::table('kategoris')->insertGetId(['nama_kategori' => 'Hobi-'.uniqid(), 'created_at' => now(), 'updated_at' => now()]);

    $kode = 'BRG996';
    DB::table('barangs')->insert([
        'kode_barang' => $kode,
        'kategori_id' => $kategoriId,
        'nama' => 'Kapal',
        'harga_beli' => 100000,
        'harga_jual' => 150000,
        'stok' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($kasir, 'sanctum')->deleteJson('/api/barangs/'.$kode);

    $response->assertStatus(403);
});

it('prevents non-admin from creating barang', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);

    $kategoriId = DB::table('kategoris')->insertGetId(['nama_kategori' => 'Makanan-'.uniqid().uniqid(), 'created_at' => now(), 'updated_at' => now()]);

    $payload = [
        'kategori_id' => $kategoriId,
        'nama' => 'Gula',
        'harga_beli' => 10000,
        'harga_jual' => 12000,
        'stok' => 10,
    ];

    $response = $this->actingAs($kasir, 'sanctum')->postJson('/api/barangs', $payload);

    $response->assertStatus(403);
});
