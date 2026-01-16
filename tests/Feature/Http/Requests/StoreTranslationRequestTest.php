<?php

use App\Http\Requests\StoreTranslationRequest;
use App\Models\Language;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Translation;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear los permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::TRANSLATIONS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::TRANSLATIONS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::TRANSLATIONS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::TRANSLATIONS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::TRANSLATIONS_VIEW,
        Permissions::TRANSLATIONS_CREATE,
        Permissions::TRANSLATIONS_EDIT,
        Permissions::TRANSLATIONS_DELETE,
    ]);
});

describe('StoreTranslationRequest - Authorization', function () {
    it('allows admin to create translations', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', []);
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('allows super-admin to create translations', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', []);
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('denies user without permissions to create translations', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', []);

        expect($request->authorize())->toBeFalse();
    });
});

describe('StoreTranslationRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('translatable_type'))->toBeTrue();
        expect($validator->errors()->has('translatable_id'))->toBeTrue();
        expect($validator->errors()->has('language_id'))->toBeTrue();
        expect($validator->errors()->has('field'))->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();
    });

    it('validates translatable_type is valid', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'translatable_type' => 'InvalidType',
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Test',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('translatable_type'))->toBeTrue();
    });

    it('validates translatable_id exists for Program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', [
            'translatable_type' => Program::class,
            'translatable_id' => 99999,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Test',
        ]);
        $request->setUserResolver(fn () => $user);
        $rules = $request->rules();

        $validator = Validator::make([
            'translatable_type' => Program::class,
            'translatable_id' => 99999,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Test',
        ], $rules);
        $validator->setData($request->all());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('translatable_id'))->toBeTrue();
    });

    it('validates translatable_id exists for Setting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', [
            'translatable_type' => Setting::class,
            'translatable_id' => 99999,
            'language_id' => $language->id,
            'field' => 'value',
            'value' => 'Test',
        ]);
        $request->setUserResolver(fn () => $user);
        $rules = $request->rules();

        $validator = Validator::make([
            'translatable_type' => Setting::class,
            'translatable_id' => 99999,
            'language_id' => $language->id,
            'field' => 'value',
            'value' => 'Test',
        ], $rules);
        $validator->setData($request->all());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('translatable_id'))->toBeTrue();
    });

    it('validates language_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => 99999,
            'field' => 'name',
            'value' => 'Test',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('language_id'))->toBeTrue();
    });

    it('validates field is valid for Program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', [
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'invalid_field',
            'value' => 'Test',
        ]);
        $request->setUserResolver(fn () => $user);
        $rules = $request->rules();

        $validator = Validator::make([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'invalid_field',
            'value' => 'Test',
        ], $rules);
        $validator->setData($request->all());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('field'))->toBeTrue();
    });

    it('validates field is valid for Setting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $setting = Setting::factory()->create();

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', [
            'translatable_type' => Setting::class,
            'translatable_id' => $setting->id,
            'language_id' => $language->id,
            'field' => 'invalid_field',
            'value' => 'Test',
        ]);
        $request->setUserResolver(fn () => $user);
        $rules = $request->rules();

        $validator = Validator::make([
            'translatable_type' => Setting::class,
            'translatable_id' => $setting->id,
            'language_id' => $language->id,
            'field' => 'invalid_field',
            'value' => 'Test',
        ], $rules);
        $validator->setData($request->all());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('field'))->toBeTrue();
    });

    it('validates unique combination of translatable_type, translatable_id, language_id and field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        // Crear traducción existente
        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Existing',
        ]);

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', [
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'New',
        ]);
        $request->setUserResolver(fn () => $user);
        $rules = $request->rules();

        $validator = Validator::make([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'New',
        ], $rules);
        $validator->setData($request->all());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();
    });

    it('allows valid translation data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Test Program',
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('handles default case in translatable_id validation when type is not Program or Setting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();

        // Create a request with invalid type that will bypass Rule::in validation
        // by directly testing the closure with a request that has invalid type
        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', [
            'translatable_type' => 'App\\Models\\SomeOtherClass', // Invalid type
            'translatable_id' => 1,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Test',
        ]);
        $request->setUserResolver(fn () => $user);
        $rules = $request->rules();

        // Create validator - translatable_type will fail, but we can test the closure logic
        // by creating a new request with valid type but then modifying it
        $testRequest = StoreTranslationRequest::create('/admin/traducciones', 'POST', [
            'translatable_type' => Program::class, // Valid type
            'translatable_id' => 1,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Test',
        ]);
        $testRequest->setUserResolver(fn () => $user);
        
        // Get the closure and bind it to a request with invalid type
        $translatableIdRule = $rules['translatable_id'];
        $closure = $translatableIdRule[2];
        
        // Create a request with invalid type to test default case
        $invalidRequest = StoreTranslationRequest::create('/admin/traducciones', 'POST', []);
        $invalidRequest->merge(['translatable_type' => 'App\\Models\\SomeOtherClass']);
        
        // Bind closure to invalid request
        $boundClosure = \Closure::bind($closure, $invalidRequest, StoreTranslationRequest::class);
        
        $failCalled = false;
        $boundClosure('translatable_id', 1, function ($message) use (&$failCalled) {
            $failCalled = true;
        });

        // The default case should set table to null, so validation should not fail
        expect($failCalled)->toBeFalse();
    });

    it('handles default case in field validation when type is not Program or Setting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        // Create a request and get the rules
        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', [
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Test',
        ]);
        $request->setUserResolver(fn () => $user);
        $rules = $request->rules();

        // Get the field validation closure
        $fieldRule = $rules['field'];
        $closure = $fieldRule[3];

        // Create a request with invalid type to test default case
        $invalidRequest = StoreTranslationRequest::create('/admin/traducciones', 'POST', []);
        $invalidRequest->merge(['translatable_type' => 'App\\Models\\SomeOtherClass']);
        
        // Bind closure to invalid request
        $boundClosure = \Closure::bind($closure, $invalidRequest, StoreTranslationRequest::class);

        // Execute the closure with invalid type to trigger default case
        $failCalled = false;
        $boundClosure('field', 'any_field', function ($message) use (&$failCalled) {
            $failCalled = true;
        });

        // The default case should set validFields to empty array, so validation should not fail
        expect($failCalled)->toBeFalse();
    });
});

describe('StoreTranslationRequest - Custom Messages', function () {
    it('returns custom error messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreTranslationRequest::create('/admin/traducciones', 'POST', []);
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('translatable_type.required');
        expect($messages)->toHaveKey('translatable_type.string');
        expect($messages)->toHaveKey('translatable_type.in');
        expect($messages)->toHaveKey('translatable_id.required');
        expect($messages)->toHaveKey('translatable_id.integer');
        expect($messages)->toHaveKey('language_id.required');
        expect($messages)->toHaveKey('language_id.integer');
        expect($messages)->toHaveKey('language_id.exists');
        expect($messages)->toHaveKey('field.required');
        expect($messages)->toHaveKey('field.string');
        expect($messages)->toHaveKey('field.max');
        expect($messages)->toHaveKey('value.required');
        expect($messages)->toHaveKey('value.string');
    });
});
