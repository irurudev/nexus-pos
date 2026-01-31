<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('generates BRG### sequence for Barang', function () {
    $code1 = \App\Models\Barang::generateKode();
    $code2 = \App\Models\Barang::generateKode();

    expect($code1)->toBe('BRG001');
    expect($code2)->toBe('BRG002');
});

it('generates PGN### sequence for Pelanggan', function () {
    $id1 = \App\Models\Pelanggan::generateId();
    $id2 = \App\Models\Pelanggan::generateId();

    expect($id1)->toBe('PGN001');
    expect($id2)->toBe('PGN002');
});
