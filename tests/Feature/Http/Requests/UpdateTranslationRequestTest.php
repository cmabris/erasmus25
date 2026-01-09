<?php

use App\Http\Requests\UpdateTranslationRequest;
use App\Models\Language;
use App\Models\Program;
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

describe('UpdateTranslationRequest - Authorization', function () {
    it('allows admin to update translations', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        $request = UpdateTranslationRequest::create("/admin/traducciones/{$translation->id}", 'PUT', []);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($translation) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', $translation);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('allows super-admin to update translations', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        $request = UpdateTranslationRequest::create("/admin/traducciones/{$translation->id}", 'PUT', []);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($translation) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', $translation);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('denies user without permissions to update translations', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        $request = UpdateTranslationRequest::create("/admin/traducciones/{$translation->id}", 'PUT', []);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($translation) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', $translation);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });
});

describe('UpdateTranslationRequest - Validation Rules', function () {
    it('validates value is required', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
        ]);

        $request = UpdateTranslationRequest::create("/admin/traducciones/{$translation->id}", 'PUT', []);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($translation) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', $translation);

            return $route;
        });
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();
    });

    it('validates unique combination excluding current translation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        // Crear traducción existente
        $existingTranslation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Existing',
        ]);

        // Crear otra traducción para actualizar con diferente campo
        $translationToUpdate = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'description',
            'value' => 'Description',
        ]);

        $request = UpdateTranslationRequest::create("/admin/traducciones/{$translationToUpdate->id}", 'PUT', []);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($translationToUpdate) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', $translationToUpdate);

            return $route;
        });
        $rules = $request->rules();

        // Intentar actualizar con la misma combinación que existingTranslation
        $validator = Validator::make([
            'value' => 'New Value',
        ], $rules);

        // Debe pasar porque es diferente (description vs name)
        expect($validator->fails())->toBeFalse();

        // Verificar que se puede actualizar la misma traducción sin conflicto
        $request2 = UpdateTranslationRequest::create("/admin/traducciones/{$existingTranslation->id}", 'PUT', []);
        $request2->setUserResolver(fn () => $user);
        $request2->setRouteResolver(function () use ($existingTranslation) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', $existingTranslation);

            return $route;
        });
        $rules2 = $request2->rules();

        $validator2 = Validator::make([
            'value' => 'Updated Value',
        ], $rules2);

        // Debe pasar porque es la misma traducción
        expect($validator2->fails())->toBeFalse();
    });

    it('allows updating value for same translation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Old Value',
        ]);

        $request = UpdateTranslationRequest::create("/admin/traducciones/{$translation->id}", 'PUT', []);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($translation) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', $translation);

            return $route;
        });
        $rules = $request->rules();

        $validator = Validator::make([
            'value' => 'New Value',
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});
