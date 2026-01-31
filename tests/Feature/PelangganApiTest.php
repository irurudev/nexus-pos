<?php

it('allows admin to create pelanggan via API', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);

    $payload = [
        'nama' => 'Jane Doe',
        'domisili' => 'Bandung',
        'jenis_kelamin' => 'WANITA',
    ];

    $response = $this->actingAs($admin, 'sanctum')->postJson('/api/pelanggans', $payload);

    $response->assertStatus(201);
    $this->assertDatabaseHas('pelanggans', ['nama' => 'Jane Doe']);
});

it('prevents non-admin from creating pelanggan', function () {
    $kasir = \App\Models\User::factory()->create(['role' => 'kasir']);

    $payload = [
        'nama' => 'John X',
        'domisili' => 'Surabaya',
        'jenis_kelamin' => 'PRIA',
    ];

    $response = $this->actingAs($kasir, 'sanctum')->postJson('/api/pelanggans', $payload);

    $response->assertStatus(403);
});

it('allows admin to update pelanggan via API', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);

    $kode = \App\Models\Pelanggan::generateId();
    \Illuminate\Support\Facades\DB::table('pelanggans')->insert([
        'id_pelanggan' => $kode,
        'nama' => 'Anto',
        'domisili' => 'Bandung',
        'jenis_kelamin' => 'PRIA',
        'poin' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin, 'sanctum')->putJson('/api/pelanggans/'.$kode, ['nama' => 'Anto Updated']);

    $response->assertStatus(200);
    $this->assertDatabaseHas('pelanggans', ['id_pelanggan' => $kode, 'nama' => 'Anto Updated']);
});

it('prevents non-admin from updating pelanggan', function () {
    $kasir = \App\Models\User::factory()->create(['role' => 'kasir']);

    $kode = \App\Models\Pelanggan::generateId();
    \Illuminate\Support\Facades\DB::table('pelanggans')->insert([
        'id_pelanggan' => $kode,
        'nama' => 'Rudi',
        'domisili' => 'Medan',
        'jenis_kelamin' => 'PRIA',
        'poin' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($kasir, 'sanctum')->putJson('/api/pelanggans/'.$kode, ['nama' => 'Rudi Updated']);

    $response->assertStatus(403);
});

it('allows admin to delete pelanggan via API', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);

    $kode = \App\Models\Pelanggan::generateId();
    \Illuminate\Support\Facades\DB::table('pelanggans')->insert([
        'id_pelanggan' => $kode,
        'nama' => 'Dedi',
        'domisili' => 'Solo',
        'jenis_kelamin' => 'PRIA',
        'poin' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin, 'sanctum')->deleteJson('/api/pelanggans/'.$kode);

    $response->assertStatus(200);
    $this->assertDatabaseMissing('pelanggans', ['id_pelanggan' => $kode]);
});

it('prevents non-admin from deleting pelanggan', function () {
    $kasir = \App\Models\User::factory()->create(['role' => 'kasir']);

    $kode = \App\Models\Pelanggan::generateId();
    \Illuminate\Support\Facades\DB::table('pelanggans')->insert([
        'id_pelanggan' => $kode,
        'nama' => 'Tono',
        'domisili' => 'Makassar',
        'jenis_kelamin' => 'PRIA',
        'poin' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($kasir, 'sanctum')->deleteJson('/api/pelanggans/'.$kode);

    $response->assertStatus(403);
});
