<?php

use Illuminate\Support\Facades\DB;

it('allows admin to view audit logs and filter them', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);
    $userA = \App\Models\User::factory()->create();
    $userB = \App\Models\User::factory()->create();

    DB::table('audit_logs')->insert([
        ['user_id' => $userA->id, 'action' => 'create', 'auditable_type' => 'App\\Models\\Barang', 'auditable_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $userB->id, 'action' => 'update', 'auditable_type' => 'App\\Models\\Pelanggan', 'auditable_id' => 2, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $res = $this->actingAs($admin, 'sanctum')->getJson('/api/audit-logs');
    $res->assertStatus(200)->assertJsonStructure(['data', 'pagination']);

    // filter by user_id
    $res2 = $this->actingAs($admin, 'sanctum')->getJson('/api/audit-logs?user_id='.$userA->id);
    $res2->assertStatus(200);
    $data = $res2->json('data');
    foreach ($data as $row) {
        expect($row['user_id'])->toBe($userA->id);
    }

    // filter by auditable_type
    $res3 = $this->actingAs($admin, 'sanctum')->getJson('/api/audit-logs?auditable_type=App\\\\Models\\\\Barang');
    $res3->assertStatus(200);
    $items = $res3->json('data');
    foreach ($items as $i) {
        expect($i['auditable_type'])->toBe('App\\Models\\Barang');
    }
});

it('prevents non-admin from viewing audit logs', function () {
    $kasir = \App\Models\User::factory()->create(['role' => 'kasir']);

    $res = $this->actingAs($kasir, 'sanctum')->getJson('/api/audit-logs');
    // prefer 403; if an internal error occurs, fail with response body for easier debugging
    $res->assertStatus(403);
});
