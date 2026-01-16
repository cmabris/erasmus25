<?php

use App\Http\Requests\UpdateCallRequest;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Program;
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
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
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
});

describe('UpdateCallRequest - Authorization', function () {
    it('authorizes user with edit permission to update call', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = UpdateCallRequest::create(
            "/admin/convocatorias/{$call->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user to update call', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = UpdateCallRequest::create(
            "/admin/convocatorias/{$call->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('denies user without edit permission', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW); // Solo view, no edit
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = UpdateCallRequest::create(
            "/admin/convocatorias/{$call->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = UpdateCallRequest::create(
            "/admin/convocatorias/{$call->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => null);
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is not Call instance', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $request = UpdateCallRequest::create(
            '/admin/convocatorias/999',
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', 'not-a-call'); // No es instancia de Call

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is null', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $request = UpdateCallRequest::create(
            '/admin/convocatorias/999',
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', null);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });
});

describe('UpdateCallRequest - Validation Rules', function () {
    it('validates slug uniqueness excluding current call', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'slug' => 'call-1',
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'slug' => 'call-2',
        ]);

        $request = UpdateCallRequest::create(
            "/admin/convocatorias/{$call1->id}",
            'PUT',
            [
                'program_id' => $program->id,
                'academic_year_id' => $academicYear->id,
                'title' => 'Test',
                'slug' => 'call-2',
                'type' => 'alumnado',
                'modality' => 'corta',
                'number_of_places' => 10,
                'destinations' => ['España'],
            ]
        );
        $request->setRouteResolver(function () use ($call1) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call1);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('allows same slug for the same call', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'slug' => 'test-slug',
        ]);

        $request = UpdateCallRequest::create(
            "/admin/convocatorias/{$call->id}",
            'PUT',
            [
                'program_id' => $program->id,
                'academic_year_id' => $academicYear->id,
                'title' => 'Test',
                'slug' => 'test-slug',
                'type' => 'alumnado',
                'modality' => 'corta',
                'number_of_places' => 10,
                'destinations' => ['España'],
            ]
        );
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('handles route parameter as Call instance in rules', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = UpdateCallRequest::create(
            "/admin/convocatorias/{$call->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call); // Instancia de Call

            return $route;
        });

        $rules = $request->rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('slug');
        expect($rules)->toHaveKey('program_id');
        expect($rules)->toHaveKey('academic_year_id');
        expect($rules)->toHaveKey('title');
        expect($rules)->toHaveKey('type');
        expect($rules)->toHaveKey('modality');
        expect($rules)->toHaveKey('number_of_places');
        expect($rules)->toHaveKey('destinations');
        expect($rules)->toHaveKey('destinations.*');
    });

    it('handles route parameter as ID in rules', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = UpdateCallRequest::create(
            "/admin/convocatorias/{$call->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call->id); // ID numérico

            return $route;
        });

        $rules = $request->rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('slug');
        expect($rules)->toHaveKey('program_id');
        expect($rules)->toHaveKey('academic_year_id');
        expect($rules)->toHaveKey('title');
        expect($rules)->toHaveKey('type');
        expect($rules)->toHaveKey('modality');
        expect($rules)->toHaveKey('number_of_places');
        expect($rules)->toHaveKey('destinations');
        expect($rules)->toHaveKey('destinations.*');
    });
});

describe('UpdateCallRequest - Custom Messages', function () {
    it('has custom error messages for all validation rules', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = UpdateCallRequest::create(
            "/admin/convocatorias/{$call->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call);

            return $route;
        });

        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('program_id.required');
        expect($messages)->toHaveKey('program_id.exists');
        expect($messages)->toHaveKey('academic_year_id.required');
        expect($messages)->toHaveKey('academic_year_id.exists');
        expect($messages)->toHaveKey('title.required');
        expect($messages)->toHaveKey('title.max');
        expect($messages)->toHaveKey('slug.unique');
        expect($messages)->toHaveKey('type.required');
        expect($messages)->toHaveKey('type.in');
        expect($messages)->toHaveKey('modality.required');
        expect($messages)->toHaveKey('modality.in');
        expect($messages)->toHaveKey('number_of_places.required');
        expect($messages)->toHaveKey('number_of_places.integer');
        expect($messages)->toHaveKey('number_of_places.min');
        expect($messages)->toHaveKey('destinations.required');
        expect($messages)->toHaveKey('destinations.array');
        expect($messages)->toHaveKey('destinations.min');
        expect($messages)->toHaveKey('destinations.*.required');
        expect($messages)->toHaveKey('destinations.*.string');
        expect($messages)->toHaveKey('destinations.*.max');
        expect($messages)->toHaveKey('estimated_start_date.date');
        expect($messages)->toHaveKey('estimated_end_date.date');
        expect($messages)->toHaveKey('estimated_end_date.after');
        expect($messages)->toHaveKey('scoring_table.array');
        expect($messages)->toHaveKey('status.in');
        expect($messages)->toHaveKey('published_at.date');
        expect($messages)->toHaveKey('closed_at.date');
    });

    it('returns translated custom messages', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = UpdateCallRequest::create(
            "/admin/convocatorias/{$call->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call', $call);

            return $route;
        });

        $messages = $request->messages();

        expect($messages['program_id.required'])->toBe(__('El programa es obligatorio.'));
        expect($messages['program_id.exists'])->toBe(__('El programa seleccionado no existe.'));
        expect($messages['title.required'])->toBe(__('El título es obligatorio.'));
        expect($messages['type.in'])->toBe(__('El tipo debe ser "alumnado" o "personal".'));
        expect($messages['modality.in'])->toBe(__('La modalidad debe ser "corta" o "larga".'));
    });
});
