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
});
