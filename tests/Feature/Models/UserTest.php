<?php

use App\Models\User;

it('generates initials from user name', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
    ]);

    expect($user->initials())->toBe('JD');
});

it('generates initials from single name', function () {
    $user = User::factory()->create([
        'name' => 'John',
    ]);

    expect($user->initials())->toBe('J');
});

it('generates initials from name with multiple words', function () {
    $user = User::factory()->create([
        'name' => 'John Michael Smith',
    ]);

    expect($user->initials())->toBe('JM');
});

it('generates initials from name with only first two words', function () {
    $user = User::factory()->create([
        'name' => 'María José García López',
    ]);

    expect($user->initials())->toBe('MJ');
});

it('handles empty name gracefully', function () {
    $user = User::factory()->create([
        'name' => '',
    ]);

    expect($user->initials())->toBe('');
});
