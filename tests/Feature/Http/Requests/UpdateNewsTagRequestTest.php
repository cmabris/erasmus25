<?php

use App\Http\Requests\UpdateNewsTagRequest;
use App\Models\NewsTag;
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
    Permission::firstOrCreate(['name' => Permissions::NEWS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
        Permissions::NEWS_DELETE,
    ]);

    // Editor tiene permisos limitados (sin delete)
    $editor->givePermissionTo([
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
    ]);
});

describe('UpdateNewsTagRequest - Authorization', function () {
    it('authorizes user with edit permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            [
                'name' => 'Updated Tag',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            [
                'name' => 'Updated Tag',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes editor user with edit permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            [
                'name' => 'Updated Tag',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('denies viewer user without edit permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            [
                'name' => 'Updated Tag',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            [
                'name' => 'Updated Tag',
            ]
        );
        $request->setUserResolver(fn () => null);
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is not NewsTag instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateNewsTagRequest::create(
            '/admin/etiquetas-noticias/999',
            'PUT',
            [
                'name' => 'Updated Tag',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', 'not-a-news-tag'); // No es instancia de NewsTag

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is null', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateNewsTagRequest::create(
            '/admin/etiquetas-noticias/999',
            'PUT',
            [
                'name' => 'Updated Tag',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', null);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });
});

describe('UpdateNewsTagRequest - Validation Rules', function () {
    it('returns rules with ignored name and slug when route parameter is NewsTag instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create([
            'name' => 'Existing Tag',
            'slug' => 'existing-tag',
        ]);

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        $rules = $request->rules();

        // Verificar que name tiene ignore
        expect($rules['name'])->toBeArray();
        $nameRule = $rules['name'][3]; // El cuarto elemento es la regla unique
        expect($nameRule)->toBeInstanceOf(\Illuminate\Validation\Rules\Unique::class);

        // Verificar que slug tiene ignore
        expect($rules['slug'])->toBeArray();
        $slugRule = $rules['slug'][3]; // El cuarto elemento es la regla unique
        expect($slugRule)->toBeInstanceOf(\Illuminate\Validation\Rules\Unique::class);
    });

    it('handles route parameter when it is not NewsTag instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateNewsTagRequest::create(
            '/admin/etiquetas-noticias/999',
            'PUT',
            []
        );
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', 999); // ID numérico, no instancia

            return $route;
        });

        $rules = $request->rules();

        // Debe retornar reglas válidas incluso cuando no es instancia
        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('slug');
    });

    it('validates required name field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name uniqueness ignoring current news tag', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag1 = NewsTag::factory()->create(['name' => 'Existing Tag']);
        $newsTag2 = NewsTag::factory()->create(['name' => 'Another Tag']);

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag2->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag2) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag2);

            return $route;
        });

        $rules = $request->rules();

        // Debe permitir usar el mismo name del newsTag2
        $validator = Validator::make([
            'name' => 'Another Tag', // Mismo name del newsTag2
        ], $rules);

        expect($validator->fails())->toBeFalse();

        // Pero no debe permitir usar el name de otro newsTag
        $validator2 = Validator::make([
            'name' => 'Existing Tag', // Name de newsTag1
        ], $rules);

        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->has('name'))->toBeTrue();
    });

    it('validates slug uniqueness ignoring current news tag', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag1 = NewsTag::factory()->create(['slug' => 'existing-slug']);
        $newsTag2 = NewsTag::factory()->create(['slug' => 'another-slug']);

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag2->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag2) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag2);

            return $route;
        });

        $rules = $request->rules();

        // Debe permitir usar el mismo slug del newsTag2
        $validator = Validator::make([
            'name' => 'Updated Tag',
            'slug' => 'another-slug', // Mismo slug del newsTag2
        ], $rules);

        expect($validator->fails())->toBeFalse();

        // Pero no debe permitir usar el slug de otro newsTag
        $validator2 = Validator::make([
            'name' => 'Updated Tag',
            'slug' => 'existing-slug', // Slug de newsTag1
        ], $rules);

        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->has('slug'))->toBeTrue();
    });

    it('validates name is string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 12345, // No es string
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'name' => str_repeat('a', 256), // Más de 255 caracteres
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates slug is string when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Updated Tag',
            'slug' => 12345, // No es string
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('validates slug max length when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Updated Tag',
            'slug' => str_repeat('a', 256), // Más de 255 caracteres
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('accepts valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Updated Tag',
            'slug' => 'updated-tag',
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('accepts valid data without slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Updated Tag',
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('UpdateNewsTagRequest - Custom Messages', function () {
    it('has custom error messages for all validation rules', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('name.required');
        expect($messages)->toHaveKey('name.string');
        expect($messages)->toHaveKey('name.max');
        expect($messages)->toHaveKey('name.unique');
        expect($messages)->toHaveKey('slug.string');
        expect($messages)->toHaveKey('slug.max');
        expect($messages)->toHaveKey('slug.unique');
    });

    it('returns translated custom messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        $messages = $request->messages();

        expect($messages['name.required'])->toBe(__('El nombre de la etiqueta es obligatorio.'));
        expect($messages['name.string'])->toBe(__('El nombre de la etiqueta debe ser un texto válido.'));
        expect($messages['name.max'])->toBe(__('El nombre de la etiqueta no puede tener más de :max caracteres.'));
        expect($messages['name.unique'])->toBe(__('Esta etiqueta ya existe.'));
        expect($messages['slug.string'])->toBe(__('El slug debe ser un texto válido.'));
        expect($messages['slug.max'])->toBe(__('El slug no puede tener más de :max caracteres.'));
        expect($messages['slug.unique'])->toBe(__('Este slug ya está en uso.'));
    });

    it('uses custom messages in validation errors', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $newsTag = NewsTag::factory()->create();

        $request = UpdateNewsTagRequest::create(
            "/admin/etiquetas-noticias/{$newsTag->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($newsTag) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/etiquetas-noticias/{news_tag}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('news_tag', $newsTag);

            return $route;
        });

        $rules = $request->rules();
        $messages = $request->messages();

        $validator = Validator::make([], $rules, $messages);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('name'))->toBe(__('El nombre de la etiqueta es obligatorio.'));
    });
});
