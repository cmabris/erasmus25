<?php

use App\Models\Setting;
use App\Models\User;

it('belongs to an updater user', function () {
    $user = User::factory()->create();
    $setting = Setting::factory()->create([
        'updated_by' => $user->id,
    ]);

    expect($setting->updater)->toBeInstanceOf(User::class)
        ->and($setting->updater->id)->toBe($user->id);
});

it('can have null updater', function () {
    $setting = Setting::factory()->create([
        'updated_by' => null,
    ]);

    expect($setting->updater)->toBeNull();
});

it('sets updated_by to null when updater user is deleted', function () {
    $user = User::factory()->create();
    $setting = Setting::factory()->create([
        'updated_by' => $user->id,
    ]);

    $user->delete();
    $setting->refresh();

    expect($setting->updated_by)->toBeNull()
        ->and($setting->updater)->toBeNull();
});

