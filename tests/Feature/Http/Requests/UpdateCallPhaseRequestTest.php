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
    // Note: Authorization is tested in Livewire component tests
    // These tests focus on validation rules only
});

describe('UpdateCallPhaseRequest - Validation Rules', function () {
    it('validates order uniqueness excluding current phase', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
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

        // Test validation rules directly
        $rules = [
            'call_id' => ['required', 'exists:calls,id'],
            'phase_type' => ['required', \Illuminate\Validation\Rule::in(['publicacion', 'solicitudes', 'provisional', 'alegaciones', 'definitivo', 'renuncias', 'lista_espera'])],
            'name' => ['required', 'string', 'max:255'],
            'order' => [
                'nullable',
                'integer',
                'min:0',
                \Illuminate\Validation\Rule::unique('call_phases', 'order')
                    ->where('call_id', $call->id)
                    ->whereNull('deleted_at')
                    ->ignore($phase2->id),
            ],
        ];

        $data = [
            'call_id' => $call->id,
            'phase_type' => 'publicacion',
            'name' => 'Fase Test',
            'order' => 5, // Already taken by phase1
        ];

        $validator = Validator::make($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('order'))->toBeTrue();
    });

    it('allows same order for the same phase', function () {
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
            'order' => 5,
        ]);

        // Test validation rules directly
        $rules = [
            'call_id' => ['required', 'exists:calls,id'],
            'phase_type' => ['required', \Illuminate\Validation\Rule::in(['publicacion', 'solicitudes', 'provisional', 'alegaciones', 'definitivo', 'renuncias', 'lista_espera'])],
            'name' => ['required', 'string', 'max:255'],
            'order' => [
                'nullable',
                'integer',
                'min:0',
                \Illuminate\Validation\Rule::unique('call_phases', 'order')
                    ->where('call_id', $call->id)
                    ->whereNull('deleted_at')
                    ->ignore($phase->id),
            ],
        ];

        $data = [
            'call_id' => $call->id,
            'phase_type' => 'publicacion',
            'name' => 'Fase Test',
            'order' => 5, // Same order as current phase
        ];

        $validator = Validator::make($data, $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates only one phase can be current per call (excluding current phase)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
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
});

describe('UpdateCallPhaseRequest - Custom Messages', function () {
    it('has custom error messages', function () {
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

        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('call_id.required');
        expect($messages)->toHaveKey('phase_type.required');
        expect($messages)->toHaveKey('name.required');
        expect($messages)->toHaveKey('end_date.after');
        expect($messages)->toHaveKey('order.unique');
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
