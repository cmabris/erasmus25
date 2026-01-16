<?php

use App\Http\Requests\PublishCallRequest;
use App\Models\Call;
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
    Permission::firstOrCreate(['name' => Permissions::CALLS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_DELETE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_PUBLISH, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::CALLS_VIEW,
        Permissions::CALLS_CREATE,
        Permissions::CALLS_EDIT,
        Permissions::CALLS_DELETE,
        Permissions::CALLS_PUBLISH,
    ]);

    // Editor tiene permisos limitados (sin publish)
    $editor->givePermissionTo([
        Permissions::CALLS_VIEW,
        Permissions::CALLS_CREATE,
        Permissions::CALLS_EDIT,
    ]);
});

describe('PublishCallRequest - Authorization', function () {
    it('authorizes user with publish permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $call = Call::factory()->create();

        $request = PublishCallRequest::create(
            "/admin/convocatorias/{$call->id}/publish",
            'POST',
            ['published_at' => now()->toDateString()]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/convocatorias/{call}/publish', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $call = Call::factory()->create();

        $request = PublishCallRequest::create(
            "/admin/convocatorias/{$call->id}/publish",
            'POST',
            ['published_at' => now()->toDateString()]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/convocatorias/{call}/publish', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('denies user without publish permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $call = Call::factory()->create();

        $request = PublishCallRequest::create(
            "/admin/convocatorias/{$call->id}/publish",
            'POST',
            ['published_at' => now()->toDateString()]
        );
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/convocatorias/{call}/publish', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $call = Call::factory()->create();

        $request = PublishCallRequest::create(
            "/admin/convocatorias/{$call->id}/publish",
            'POST',
            ['published_at' => now()->toDateString()]
        );
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/convocatorias/{call}/publish', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is not Call instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = PublishCallRequest::create(
            '/admin/convocatorias/1/publish',
            'POST',
            ['published_at' => now()->toDateString()]
        );
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/convocatorias/{call}/publish', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', 'not-a-call-instance');

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is null', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = PublishCallRequest::create(
            '/admin/convocatorias/1/publish',
            'POST',
            ['published_at' => now()->toDateString()]
        );
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/convocatorias/{call}/publish', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', null);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });
});

describe('PublishCallRequest - Validation Rules', function () {
    it('allows published_at to be nullable', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = PublishCallRequest::create('/admin/convocatorias/1/publish', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('validates published_at as valid date when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = PublishCallRequest::create('/admin/convocatorias/1/publish', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'published_at' => '2025-01-15',
        ], $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('validates published_at accepts different date formats', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = PublishCallRequest::create('/admin/convocatorias/1/publish', 'POST', []);
        $rules = $request->rules();

        $validDates = [
            '2025-01-15',
            '2025-01-15 10:30:00',
            '2025-01-15T10:30:00',
            now()->toDateString(),
            now()->toDateTimeString(),
        ];

        foreach ($validDates as $date) {
            $validator = Validator::make([
                'published_at' => $date,
            ], $rules);

            expect($validator->passes())->toBeTrue("Failed for date: {$date}");
        }
    });

    it('rejects invalid date format', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = PublishCallRequest::create('/admin/convocatorias/1/publish', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'published_at' => 'not-a-date',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('published_at'))->toBeTrue();
    });

    it('rejects non-date values', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = PublishCallRequest::create('/admin/convocatorias/1/publish', 'POST', []);
        $rules = $request->rules();

        $invalidValues = [
            '123',
            'text',
            'true',
            'false',
            [],
        ];

        foreach ($invalidValues as $value) {
            $validator = Validator::make([
                'published_at' => $value,
            ], $rules);

            expect($validator->fails())->toBeTrue("Should fail for value: " . json_encode($value));
            expect($validator->errors()->has('published_at'))->toBeTrue();
        }
    });
});

describe('PublishCallRequest - Custom Messages', function () {
    it('has custom error message for published_at.date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = PublishCallRequest::create('/admin/convocatorias/1/publish', 'POST', []);
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('published_at.date');
        expect($messages['published_at.date'])->toBe(__('La fecha de publicación debe ser una fecha válida.'));
    });

    it('returns custom messages array', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = PublishCallRequest::create('/admin/convocatorias/1/publish', 'POST', []);
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect(count($messages))->toBe(1);
    });
});
