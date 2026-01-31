<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Kategori;
use App\Models\User;

uses(TestCase::class, RefreshDatabase::class);

it('allows admin to update kategori via API', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $kategori = Kategori::create(['nama_kategori' => 'Kategori-' . uniqid()]);

    $payload = ['nama_kategori' => 'Kategori-Updated-' . uniqid()];

    $response = $this->actingAs($admin, 'sanctum')->putJson('/api/kategoris/'.$kategori->id, $payload);

    $response->assertStatus(200);
    $this->assertDatabaseHas('kategoris', ['id' => $kategori->id, 'nama_kategori' => $payload['nama_kategori']]);
});

it('prevents non-admin from updating kategori', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);

    $kategori = Kategori::create(['nama_kategori' => 'Kategori-' . uniqid()]);

    $payload = ['nama_kategori' => 'Kategori-Updated-' . uniqid()];

    $response = $this->actingAs($kasir, 'sanctum')->putJson('/api/kategoris/'.$kategori->id, $payload);

    $response->assertStatus(403);
});