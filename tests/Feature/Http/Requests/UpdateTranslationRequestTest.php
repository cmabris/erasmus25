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

    it('returns false when route parameter is not Translation instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateTranslationRequest::create('/admin/traducciones/999', 'PUT', []);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', 'not-a-translation'); // No es instancia de Translation

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        $request = UpdateTranslationRequest::create("/admin/traducciones/{$translation->id}", 'PUT', []);
        $request->setUserResolver(fn () => null);
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

    it('validates custom rule prevents duplicate translation combination', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        // Crear traducción existente con campo 'name'
        $existingTranslation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Existing',
        ]);

        // Crear otra traducción para actualizar con campo 'description' (diferente)
        $translationToUpdate = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'description', // Campo diferente
            'value' => 'Description',
        ]);

        $request = UpdateTranslationRequest::create("/admin/traducciones/{$translationToUpdate->id}", 'PUT', [
            'value' => 'New Value',
        ]);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($translationToUpdate) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', $translationToUpdate);

            return $route;
        });
        $rules = $request->rules();

        // Extraer la validación personalizada y probarla directamente
        $valueRules = $rules['value'];
        $customRule = null;
        foreach ($valueRules as $rule) {
            if (is_callable($rule) && !is_string($rule) && !($rule instanceof \Illuminate\Contracts\Validation\Rule)) {
                $customRule = $rule;
                break;
            }
        }

        expect($customRule)->not->toBeNull();

        // Probar la validación personalizada directamente simulando que existe
        // una traducción con la misma combinación (usando los valores de existingTranslation)
        // Para esto, necesitamos crear un nuevo request con una traducción que tenga
        // la misma combinación que existingTranslation pero diferente ID
        $translationWithSameCombination = Translation::factory()->make([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name', // Mismo campo que existingTranslation
        ]);
        $translationWithSameCombination->id = $translationToUpdate->id; // Usar el ID de translationToUpdate

        $request2 = UpdateTranslationRequest::create("/admin/traducciones/{$translationToUpdate->id}", 'PUT', [
            'value' => 'New Value',
        ]);
        $request2->setRouteResolver(function () use ($translationWithSameCombination) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', $translationWithSameCombination);

            return $route;
        });
        $rules2 = $request2->rules();

        $valueRules2 = $rules2['value'];
        $customRule2 = null;
        foreach ($valueRules2 as $rule) {
            if (is_callable($rule) && !is_string($rule) && !($rule instanceof \Illuminate\Contracts\Validation\Rule)) {
                $customRule2 = $rule;
                break;
            }
        }

        // Ejecutar la validación personalizada
        $failCalled = false;
        $failMessage = '';
        $fail = function ($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };

        $customRule2('value', 'New Value', $fail);

        expect($failCalled)->toBeTrue();
        expect($failMessage)->toBe(__('Ya existe una traducción para esta combinación de modelo, idioma y campo.'));
    });

    it('handles route parameter as ID in rules', function () {
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

        $request = UpdateTranslationRequest::create("/admin/traducciones/{$translation->id}", 'PUT', [
            'value' => 'New Value',
        ]);
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($translation) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', $translation->id); // ID numérico en lugar de instancia

            return $route;
        });

        $rules = $request->rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('value');
    });

    it('validates value is string', function () {
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

        $validator = Validator::make([
            'value' => 12345, // No es string
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();
    });
});

describe('UpdateTranslationRequest - Custom Messages', function () {
    it('has custom error messages for all validation rules', function () {
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
        $request->setRouteResolver(function () use ($translation) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', $translation);

            return $route;
        });

        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('value.required');
        expect($messages)->toHaveKey('value.string');
    });

    it('returns translated custom messages', function () {
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
        $request->setRouteResolver(function () use ($translation) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/traducciones/{translation}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('translation', $translation);

            return $route;
        });

        $messages = $request->messages();

        expect($messages['value.required'])->toBe(__('El valor de la traducción es obligatorio.'));
        expect($messages['value.string'])->toBe(__('El valor de la traducción debe ser un texto válido.'));
    });
});
