<?php

use App\Models\Barang;
use App\Models\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('continues sequence beyond 999 without collision', function () {
    // Seed sequence with 999
    DB::table('sequences')->insert(['name' => 'barang', 'value' => 999, 'created_at' => now(), 'updated_at' => now()]);

    // Using model generator should produce BRG1000 (first increment)
    $code = Barang::generateKode();
    expect($code)->toBe('BRG1000');

    // Next call to sequence should produce 1001
    $next = Sequence::next('barang');
    expect($next)->toBe(1001);
});

it('generates unique consecutive sequence numbers', function () {
    $a = Sequence::next('u');
    $b = Sequence::next('u');
    $c = Sequence::next('u');

    expect($a)->toBe(1);
    expect($b)->toBe(2);
    expect($c)->toBe(3);
});
