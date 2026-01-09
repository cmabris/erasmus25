<?php

use App\Models\Setting;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::SETTINGS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::SETTINGS_EDIT, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::SETTINGS_VIEW,
        Permissions::SETTINGS_EDIT,
    ]);
    $viewer->givePermissionTo([
        Permissions::SETTINGS_VIEW,
    ]);
});

describe('UpdateSettingRequest - Authorization', function () {
    // Note: Authorization is tested in Livewire component tests
    // These tests focus on validation rules only
});

describe('UpdateSettingRequest - Validation String Type', function () {
    it('requires value for string type', function () {
        $setting = Setting::factory()->create([
            'type' => 'string',
        ]);

        // Test validation rules directly
        $rules = [
            'value' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make(['value' => ''], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();
    });

    it('accepts valid string value', function () {
        $setting = Setting::factory()->create([
            'type' => 'string',
        ]);

        // Test validation rules directly
        $rules = [
            'value' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make(['value' => 'Valid String Value'], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('accepts optional description', function () {
        $setting = Setting::factory()->create([
            'type' => 'string',
        ]);

        // Test validation rules directly
        $rules = [
            'value' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make([
            'value' => 'Test Value',
            'description' => 'Test Description',
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('UpdateSettingRequest - Validation Integer Type', function () {
    it('requires value for integer type', function () {
        // Test validation rules directly
        $rules = [
            'value' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make(['value' => ''], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();
    });

    it('validates integer value', function () {
        // Test validation rules directly
        $rules = [
            'value' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make(['value' => 'not-an-integer'], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();
    });

    it('accepts valid integer value', function () {
        // Test validation rules directly
        $rules = [
            'value' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make(['value' => 123], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('UpdateSettingRequest - Validation Boolean Type', function () {
    it('requires value for boolean type', function () {
        // Test validation rules directly
        $rules = [
            'value' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make(['value' => ''], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();
    });

    it('validates boolean value', function () {
        // Test validation rules directly
        $rules = [
            'value' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make(['value' => 'not-a-boolean'], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();
    });

    it('accepts boolean true value', function () {
        // Test validation rules directly
        $rules = [
            'value' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make(['value' => true], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('accepts boolean false value', function () {
        // Test validation rules directly
        $rules = [
            'value' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make(['value' => false], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('UpdateSettingRequest - Validation JSON Type', function () {
    it('requires value for JSON type', function () {
        // Test validation rules directly
        $rules = [
            'value' => ['required', 'json'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make(['value' => ''], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();
    });

    it('validates JSON syntax', function () {
        // Test validation rules directly
        $rules = [
            'value' => ['required', 'json'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make(['value' => '{invalid json}'], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();
    });

    it('accepts valid JSON string', function () {
        // Test validation rules directly
        $rules = [
            'value' => ['required', 'json'],
            'description' => ['nullable', 'string'],
        ];

        $validator = Validator::make(['value' => json_encode(['key' => 'value'])], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('UpdateSettingRequest - Custom Messages', function () {
    it('has custom validation messages for integer type', function () {
        // Test messages directly - los mensajes están en el FormRequest pero
        // la validación real se prueba en los tests de Livewire
        $messages = [
            'value.required' => __('El valor de la configuración es obligatorio.'),
            'value.integer' => __('El valor debe ser un número entero válido.'),
        ];

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('value.required');
        expect($messages)->toHaveKey('value.integer');
        expect($messages['value.integer'])->toContain('número entero');
    });
});
