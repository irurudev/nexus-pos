<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

it('allows admin to create kategori via API', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);

    $payload = ['nama_kategori' => 'Kategori-' . uniqid()];

    $response = $this->actingAs($admin, 'sanctum')->postJson('/api/kategoris', $payload);

    $response->assertStatus(201);
    $this->assertDatabaseHas('kategoris', ['nama_kategori' => $payload['nama_kategori']]);
});

it('prevents non-admin from creating kategori', function () {
    $kasir = \App\Models\User::factory()->create(['role' => 'kasir']);

    $payload = ['nama_kategori' => 'Kategori-' . uniqid()];

    $response = $this->actingAs($kasir, 'sanctum')->postJson('/api/kategoris', $payload);

    $response->assertStatus(403);
});
