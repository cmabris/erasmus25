<?php

use App\Support\Roles;

it('defines all role constants correctly', function () {
    expect(Roles::SUPER_ADMIN)->toBe('super-admin')
        ->and(Roles::ADMIN)->toBe('admin')
        ->and(Roles::EDITOR)->toBe('editor')
        ->and(Roles::VIEWER)->toBe('viewer');
});

it('returns all roles when calling all()', function () {
    $roles = Roles::all();

    expect($roles)->toBeArray()
        ->toHaveCount(4)
        ->toContain(Roles::SUPER_ADMIN)
        ->toContain(Roles::ADMIN)
        ->toContain(Roles::EDITOR)
        ->toContain(Roles::VIEWER);
});

it('returns administrative roles when calling administrative()', function () {
    $administrativeRoles = Roles::administrative();

    expect($administrativeRoles)->toBeArray()
        ->toHaveCount(2)
        ->toContain(Roles::SUPER_ADMIN)
        ->toContain(Roles::ADMIN)
        ->not->toContain(Roles::EDITOR)
        ->not->toContain(Roles::VIEWER);
});

it('correctly identifies administrative roles', function () {
    expect(Roles::isAdministrative(Roles::SUPER_ADMIN))->toBeTrue()
        ->and(Roles::isAdministrative(Roles::ADMIN))->toBeTrue()
        ->and(Roles::isAdministrative(Roles::EDITOR))->toBeFalse()
        ->and(Roles::isAdministrative(Roles::VIEWER))->toBeFalse();
});

it('returns false for non-existent roles', function () {
    expect(Roles::isAdministrative('non-existent-role'))->toBeFalse();
});


