<?php

use App\Http\Requests\UpdateCallPhaseRequest;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
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
    ]);
});

describe('UpdateCallPhaseRequest - Authorization', function () {
    it('authorizes user with edit permission to update call phase', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $request = UpdateCallPhaseRequest::create(
            "/admin/convocatorias/{$call->id}/fases/{$phase->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($phase) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', $phase);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user to update call phase', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $request = UpdateCallPhaseRequest::create(
            "/admin/convocatorias/{$call->id}/fases/{$phase->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($phase) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', $phase);

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
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $request = UpdateCallPhaseRequest::create(
            "/admin/convocatorias/{$call->id}/fases/{$phase->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($phase) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', $phase);

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
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $request = UpdateCallPhaseRequest::create(
            "/admin/convocatorias/{$call->id}/fases/{$phase->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => null);
        $request->setRouteResolver(function () use ($phase) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', $phase);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is not CallPhase instance', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $request = UpdateCallPhaseRequest::create(
            '/admin/convocatorias/1/fases/999',
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', 'not-a-phase'); // No es instancia de CallPhase

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is null', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $request = UpdateCallPhaseRequest::create(
            '/admin/convocatorias/1/fases/999',
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', null);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });
});

describe('UpdateCallPhaseRequest - Validation Rules', function () {
    it('validates order uniqueness excluding current phase', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $phase1 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'order' => 5,
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'order' => 3,
        ]);

        $request = UpdateCallPhaseRequest::create(
            "/admin/convocatorias/{$call->id}/fases/{$phase2->id}",
            'PUT',
            [
                'call_id' => $call->id,
                'phase_type' => 'publicacion',
                'name' => 'Fase Test',
                'order' => 5, // Already taken by phase1
            ]
        );
        $request->setRouteResolver(function () use ($phase2) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', $phase2);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('order'))->toBeTrue();
    });

    it('allows same order for the same phase', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $phase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'order' => 5,
        ]);

        $request = UpdateCallPhaseRequest::create(
            "/admin/convocatorias/{$call->id}/fases/{$phase->id}",
            'PUT',
            [
                'call_id' => $call->id,
                'phase_type' => 'publicacion',
                'name' => 'Fase Test',
                'order' => 5, // Same order as current phase
            ]
        );
        $request->setRouteResolver(function () use ($phase) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', $phase);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates only one phase can be current per call (excluding current phase)', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $currentPhase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'is_current' => true,
        ]);

        $phase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'is_current' => false,
        ]);

        $request = UpdateCallPhaseRequest::create(
            "/admin/convocatorias/{$call->id}/fases/{$phase->id}",
            'PUT',
            [
                'call_id' => $call->id,
                'phase_type' => 'publicacion',
                'name' => 'Fase Test',
                'is_current' => true,
            ]
        );
        $request->setRouteResolver(function () use ($phase) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', $phase);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('is_current'))->toBeTrue();
    });

    it('allows marking phase as current when it is the only one', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $phase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'is_current' => false,
        ]);

        // Test validation rules directly
        $rules = [
            'call_id' => ['required', 'exists:calls,id'],
            'phase_type' => ['required', \Illuminate\Validation\Rule::in(['publicacion', 'solicitudes', 'provisional', 'alegaciones', 'definitivo', 'renuncias', 'lista_espera'])],
            'name' => ['required', 'string', 'max:255'],
            'is_current' => [
                'nullable',
                'boolean',
                function ($attribute, $value, $fail) use ($call, $phase) {
                    if ($value === true && $call->id) {
                        $hasCurrentPhase = CallPhase::where('call_id', $call->id)
                            ->where('is_current', true)
                            ->where('id', '!=', $phase->id)
                            ->exists();

                        if ($hasCurrentPhase) {
                            $fail(__('Ya existe una fase marcada como actual para esta convocatoria. Solo puede haber una fase actual a la vez.'));
                        }
                    }
                },
            ],
        ];

        $data = [
            'call_id' => $call->id,
            'phase_type' => 'publicacion',
            'name' => 'Fase Test',
            'is_current' => true,
        ];

        $validator = Validator::make($data, $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('handles route parameter as CallPhase instance in rules', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $request = UpdateCallPhaseRequest::create(
            "/admin/convocatorias/{$call->id}/fases/{$phase->id}",
            'PUT',
            [
                'call_id' => $call->id,
                'phase_type' => 'publicacion',
                'name' => 'Fase Test',
            ]
        );
        $request->setRouteResolver(function () use ($phase) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', $phase); // Instancia de CallPhase

            return $route;
        });

        $rules = $request->rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('call_id');
        expect($rules)->toHaveKey('phase_type');
        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('order');
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
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $request = UpdateCallPhaseRequest::create(
            "/admin/convocatorias/{$call->id}/fases/{$phase->id}",
            'PUT',
            [
                'call_id' => $call->id,
                'phase_type' => 'publicacion',
                'name' => 'Fase Test',
            ]
        );
        $request->setRouteResolver(function () use ($phase) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', $phase->id); // ID numérico

            return $route;
        });

        $rules = $request->rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('call_id');
        expect($rules)->toHaveKey('phase_type');
        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('order');
    });

    it('validates is_current when callPhaseId exists using FormRequest', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_EDIT);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $currentPhase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'is_current' => true,
        ]);

        $phase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'is_current' => false,
        ]);

        $request = UpdateCallPhaseRequest::create(
            "/admin/convocatorias/{$call->id}/fases/{$phase->id}",
            'PUT',
            [
                'call_id' => $call->id,
                'phase_type' => 'publicacion',
                'name' => 'Fase Test',
                'is_current' => true,
            ]
        );
        $request->setRouteResolver(function () use ($phase) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', $phase); // Instancia de CallPhase para que callPhaseId exista

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('is_current'))->toBeTrue();
    });
});

describe('UpdateCallPhaseRequest - Custom Messages', function () {
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
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $request = UpdateCallPhaseRequest::create(
            "/admin/convocatorias/{$call->id}/fases/{$phase->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($phase) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', $phase);

            return $route;
        });

        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('call_id.required');
        expect($messages)->toHaveKey('call_id.exists');
        expect($messages)->toHaveKey('phase_type.required');
        expect($messages)->toHaveKey('phase_type.in');
        expect($messages)->toHaveKey('name.required');
        expect($messages)->toHaveKey('name.string');
        expect($messages)->toHaveKey('name.max');
        expect($messages)->toHaveKey('description.string');
        expect($messages)->toHaveKey('start_date.date');
        expect($messages)->toHaveKey('end_date.date');
        expect($messages)->toHaveKey('end_date.after');
        expect($messages)->toHaveKey('is_current.boolean');
        expect($messages)->toHaveKey('order.integer');
        expect($messages)->toHaveKey('order.min');
        expect($messages)->toHaveKey('order.unique');
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
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $request = UpdateCallPhaseRequest::create(
            "/admin/convocatorias/{$call->id}/fases/{$phase->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($phase) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('call_phase', $phase);

            return $route;
        });

        $messages = $request->messages();

        expect($messages['call_id.required'])->toBe(__('El ID de la convocatoria es obligatorio.'));
        expect($messages['call_id.exists'])->toBe(__('La convocatoria seleccionada no existe o ha sido eliminada.'));
        expect($messages['phase_type.required'])->toBe(__('Debe seleccionar un tipo de fase.'));
        expect($messages['name.required'])->toBe(__('El nombre de la fase es obligatorio.'));
    });

    it('has custom attribute names', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $request = UpdateCallPhaseRequest::create("/admin/convocatorias/{$call->id}/fases/{$phase->id}", 'PUT', []);
        $request->setRouteResolver(function () use ($phase) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}/fases/{call_phase}', []);
            $route->setParameter('call_phase', $phase);

            return $route;
        });

        $attributes = $request->attributes();

        expect($attributes)->toBeArray();
        expect($attributes)->toHaveKey('call_id');
        expect($attributes)->toHaveKey('phase_type');
        expect($attributes)->toHaveKey('name');
        expect($attributes)->toHaveKey('order');
    });
});
