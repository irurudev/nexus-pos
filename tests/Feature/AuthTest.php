<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

it('allows user to login with correct credentials', function () {
    $password = 'secret123';

    $user = User::factory()->create([
        'password' => Hash::make($password),
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => $password,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email', 'role'], 'token']]);
});

it('rejects invalid password', function () {
    $user = User::factory()->create(['password' => Hash::make('rightpass'), 'is_active' => true]);

    $response = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrongpass']);

    $response->assertStatus(401);
});

it('rejects inactive user', function () {
    $pw = 'inactivepw';
    $user = User::factory()->create(['password' => Hash::make($pw), 'is_active' => false]);

    $response = $this->postJson('/api/login', ['email' => $user->email, 'password' => $pw]);

    $response->assertStatus(401);
});

it('returns validation error when missing fields', function () {
    $response = $this->postJson('/api/login', []);

    $response->assertStatus(422);
});

it('logout revokes token (token row removed)', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)->postJson('/api/logout');
    $response->assertStatus(200);

    $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id, 'name' => 'test-token']);
});

it('me returns user data when authenticated and 401 for invalid token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('t')->plainTextToken;

    $res = $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/me');
    $res->assertStatus(200)->assertJsonFragment(['email' => $user->email]);

    $unauth = $this->withoutHeader('Authorization')->getJson('/api/me');
    $unauth->assertStatus(401);
});
