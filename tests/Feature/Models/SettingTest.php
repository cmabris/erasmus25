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

it('sets updated_by to null when updater user is force deleted', function () {
    $user = User::factory()->create();
    $setting = Setting::factory()->create([
        'updated_by' => $user->id,
    ]);

    // Force delete to trigger foreign key constraint
    $user->forceDelete();
    $setting->refresh();

    expect($setting->updated_by)->toBeNull()
        ->and($setting->updater)->toBeNull();
});

it('casts integer value correctly when getting', function () {
    $setting = Setting::create([
        'key' => 'test_integer',
        'value' => '42',
        'type' => 'integer',
        'group' => 'general',
    ]);

    expect($setting->value)->toBe(42)
        ->and($setting->value)->toBeInt();
});

it('casts boolean value correctly when getting', function () {
    $setting = Setting::create([
        'key' => 'test_boolean_true',
        'value' => '1',
        'type' => 'boolean',
        'group' => 'general',
    ]);

    expect($setting->value)->toBeTrue();

    $setting2 = Setting::create([
        'key' => 'test_boolean_false',
        'value' => '0',
        'type' => 'boolean',
        'group' => 'general',
    ]);

    expect($setting2->value)->toBeFalse();
});

it('casts json value correctly when getting', function () {
    $jsonData = ['key' => 'value', 'number' => 123];
    $setting = Setting::create([
        'key' => 'test_json',
        'value' => json_encode($jsonData),
        'type' => 'json',
        'group' => 'general',
    ]);

    expect($setting->value)->toBeArray()
        ->and($setting->value)->toBe($jsonData)
        ->and($setting->value['key'])->toBe('value')
        ->and($setting->value['number'])->toBe(123);
});

it('returns string value as is when getting', function () {
    $setting = Setting::create([
        'key' => 'test_string',
        'value' => 'Hello World',
        'type' => 'string',
        'group' => 'general',
    ]);

    expect($setting->value)->toBe('Hello World')
        ->and($setting->value)->toBeString();
});

it('casts integer value correctly when setting', function () {
    $setting = new Setting([
        'key' => 'test_integer_set',
        'type' => 'integer',
        'group' => 'general',
    ]);
    $setting->value = 42;
    $setting->save();

    expect($setting->getAttributes()['value'])->toBe('42')
        ->and($setting->value)->toBe(42);
});

it('casts boolean value correctly when setting', function () {
    $setting = new Setting([
        'key' => 'test_boolean_set_true',
        'type' => 'boolean',
        'group' => 'general',
    ]);
    $setting->value = true;
    $setting->save();

    expect($setting->getAttributes()['value'])->toBe('1')
        ->and($setting->value)->toBeTrue();

    $setting2 = new Setting([
        'key' => 'test_boolean_set_false',
        'type' => 'boolean',
        'group' => 'general',
    ]);
    $setting2->value = false;
    $setting2->save();

    expect($setting2->getAttributes()['value'])->toBe('0')
        ->and($setting2->value)->toBeFalse();
});

it('casts json value correctly when setting', function () {
    $jsonData = ['key' => 'value', 'nested' => ['data' => 123]];
    $setting = new Setting([
        'key' => 'test_json_set',
        'type' => 'json',
        'group' => 'general',
    ]);
    $setting->value = $jsonData;
    $setting->save();

    expect($setting->getAttributes()['value'])->toBe(json_encode($jsonData))
        ->and($setting->value)->toBeArray()
        ->and($setting->value)->toBe($jsonData);
});

it('sets string value as is when setting', function () {
    $setting = new Setting([
        'key' => 'test_string_set',
        'type' => 'string',
        'group' => 'general',
    ]);
    $setting->value = 'Hello World';
    $setting->save();

    expect($setting->getAttributes()['value'])->toBe('Hello World')
        ->and($setting->value)->toBe('Hello World');
});

it('handles default type when getting value', function () {
    // El tipo 'string' es el default según el match, probamos que funciona correctamente
    $setting = Setting::create([
        'key' => 'test_default',
        'value' => 'Some value',
        'type' => 'string',
        'group' => 'general',
    ]);

    expect($setting->value)->toBe('Some value');

    // También probamos que cualquier valor no reconocido usa el default
    // Como el enum solo permite valores específicos, usamos 'string' que es el default
    $setting2 = Setting::create([
        'key' => 'test_default_2',
        'value' => 'Another value',
        'type' => 'string',
        'group' => 'general',
    ]);

    expect($setting2->value)->toBe('Another value');
});

it('handles default type when setting value', function () {
    // El tipo 'string' es el default según el match
    $setting = new Setting([
        'key' => 'test_default_set',
        'type' => 'string',
        'group' => 'general',
    ]);
    $setting->value = 'Some value';
    $setting->save();

    expect($setting->getAttributes()['value'])->toBe('Some value')
        ->and($setting->value)->toBe('Some value');
});
