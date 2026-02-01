<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('allows admin to create, update, list, and delete users', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin, 'sanctum');

    // create
    $payload = [
        'name' => 'New User',
        'username' => 'newuser',
        'email' => 'newuser@example.com',
        'password' => 'secret123',
        'role' => 'kasir',
        'is_active' => true,
    ];

    $createResp = $this->postJson('/api/users', $payload);
    $createResp->assertStatus(201);
    $json = $createResp->json();
    expect($json['data']['email'])->toBe('newuser@example.com');

    $userId = $json['data']['id'];

    // list
    $listResp = $this->getJson('/api/users');
    $listResp->assertStatus(200);

    // update
    $updateResp = $this->putJson("/api/users/{$userId}", [
        'name' => 'Updated',
        'username' => 'newuser',
        'email' => 'newuser@example.com',
        'role' => 'kasir',
    ]);
    $updateResp->assertStatus(200);

    // delete
    $delResp = $this->deleteJson("/api/users/{$userId}");
    $delResp->assertStatus(200);

    // confirm deletion
    $showResp = $this->getJson("/api/users/{$userId}");
    $showResp->assertStatus(404);
});

it('prevents non-admin from managing users', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);
    $this->actingAs($kasir, 'sanctum');

    $resp = $this->postJson('/api/users', [
        'name' => 'Nope',
        'username' => 'nope',
        'email' => 'nope@example.com',
        'password' => 'secret',
        'role' => 'kasir',
    ]);

    $resp->assertStatus(403);
});