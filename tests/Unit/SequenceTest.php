<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('increments sequence and returns consecutive values', function () {
    $first = \App\Models\Sequence::next('test_seq');
    $second = \App\Models\Sequence::next('test_seq');

    expect($first)->toBe(1);
    expect($second)->toBe(2);
});

it('creates separate sequences for different names', function () {
    $a1 = \App\Models\Sequence::next('a');
    $b1 = \App\Models\Sequence::next('b');
    $a2 = \App\Models\Sequence::next('a');

    expect($a1)->toBe(1);
    expect($b1)->toBe(1);
    expect($a2)->toBe(2);
});
