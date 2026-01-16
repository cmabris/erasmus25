<?php

use App\Http\Requests\StoreResolutionRequest;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    Storage::fake('public');
});

describe('StoreResolutionRequest - Authorization', function () {
    it('authorizes user with create permission to store resolution', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_CREATE);
        $this->actingAs($user);

        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            []
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user to store resolution', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            []
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('denies user without create permission', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW); // Solo view, no create
        $this->actingAs($user);

        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            []
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            []
        );
        $request->setUserResolver(fn () => null);

        expect($request->authorize())->toBeFalse();
    });
});

describe('StoreResolutionRequest - Validation Rules', function () {
    it('validates call_phase_id belongs to call_id', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_CREATE);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $phase1 = CallPhase::factory()->create(['call_id' => $call1->id]);
        $phase2 = CallPhase::factory()->create(['call_id' => $call2->id]);

        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            [
                'call_id' => $call1->id,
                'call_phase_id' => $phase2->id, // Phase belongs to call2, not call1
                'type' => 'provisional',
                'title' => 'Test Resolution',
                'official_date' => '2024-01-01',
            ]
        );

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('call_phase_id'))->toBeTrue();
    });

    it('allows call_phase_id when it belongs to call_id', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_CREATE);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            [
                'call_id' => $call->id,
                'call_phase_id' => $phase->id,
                'type' => 'provisional',
                'title' => 'Test Resolution',
                'official_date' => '2024-01-01',
            ]
        );

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates all required fields', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_CREATE);
        $this->actingAs($user);

        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            []
        );

        $rules = $request->rules();
        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('call_id'))->toBeTrue();
        expect($validator->errors()->has('call_phase_id'))->toBeTrue();
        expect($validator->errors()->has('type'))->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
        expect($validator->errors()->has('official_date'))->toBeTrue();
    });

    it('validates type enum values', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_CREATE);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            [
                'call_id' => $call->id,
                'call_phase_id' => $phase->id,
                'type' => 'invalid-type',
                'title' => 'Test Resolution',
                'official_date' => '2024-01-01',
            ]
        );

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('type'))->toBeTrue();
    });

    it('allows valid type enum values', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_CREATE);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        foreach (['provisional', 'definitivo', 'alegaciones'] as $type) {
            $request = StoreResolutionRequest::create(
                '/admin/convocatorias/1/resoluciones',
                'POST',
                [
                    'call_id' => $call->id,
                    'call_phase_id' => $phase->id,
                    'type' => $type,
                    'title' => 'Test Resolution',
                    'official_date' => '2024-01-01',
                ]
            );

            $rules = $request->rules();
            $validator = Validator::make($request->all(), $rules);

            expect($validator->fails())->toBeFalse();
        }
    });

    it('validates title max length', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_CREATE);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            [
                'call_id' => $call->id,
                'call_phase_id' => $phase->id,
                'type' => 'provisional',
                'title' => str_repeat('a', 256), // Exceeds max:255
                'official_date' => '2024-01-01',
            ]
        );

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
    });

    it('validates pdfFile file type and size', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_CREATE);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('document.txt', 100);
        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            [
                'call_id' => $call->id,
                'call_phase_id' => $phase->id,
                'type' => 'provisional',
                'title' => 'Test Resolution',
                'official_date' => '2024-01-01',
                'pdfFile' => $invalidFile,
            ]
        );

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('pdfFile'))->toBeTrue();

        // Test file size exceeds limit
        $largeFile = UploadedFile::fake()->create('document.pdf', 10241); // Exceeds 10MB (10240KB)
        $request2 = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            [
                'call_id' => $call->id,
                'call_phase_id' => $phase->id,
                'type' => 'provisional',
                'title' => 'Test Resolution',
                'official_date' => '2024-01-01',
                'pdfFile' => $largeFile,
            ]
        );

        $rules2 = $request2->rules();
        $validator2 = Validator::make($request2->all(), $rules2);

        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->has('pdfFile'))->toBeTrue();
    });

    it('allows valid pdfFile', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_CREATE);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $pdfFile = UploadedFile::fake()->create('document.pdf', 100);
        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            [
                'call_id' => $call->id,
                'call_phase_id' => $phase->id,
                'type' => 'provisional',
                'title' => 'Test Resolution',
                'official_date' => '2024-01-01',
                'pdfFile' => $pdfFile,
            ]
        );

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('StoreResolutionRequest - Custom Messages', function () {
    it('has custom error messages for all validation rules', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_CREATE);
        $this->actingAs($user);

        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            []
        );

        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('call_id.required');
        expect($messages)->toHaveKey('call_id.exists');
        expect($messages)->toHaveKey('call_phase_id.required');
        expect($messages)->toHaveKey('call_phase_id.exists');
        expect($messages)->toHaveKey('type.required');
        expect($messages)->toHaveKey('type.in');
        expect($messages)->toHaveKey('title.required');
        expect($messages)->toHaveKey('title.string');
        expect($messages)->toHaveKey('title.max');
        expect($messages)->toHaveKey('description.string');
        expect($messages)->toHaveKey('evaluation_procedure.string');
        expect($messages)->toHaveKey('official_date.required');
        expect($messages)->toHaveKey('official_date.date');
        expect($messages)->toHaveKey('published_at.date');
        expect($messages)->toHaveKey('pdfFile.file');
        expect($messages)->toHaveKey('pdfFile.mimes');
        expect($messages)->toHaveKey('pdfFile.max');
    });

    it('returns translated custom messages', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_CREATE);
        $this->actingAs($user);

        $request = StoreResolutionRequest::create(
            '/admin/convocatorias/1/resoluciones',
            'POST',
            []
        );

        $messages = $request->messages();

        expect($messages['call_id.required'])->toBe(__('El ID de la convocatoria es obligatorio.'));
        expect($messages['call_id.exists'])->toBe(__('La convocatoria seleccionada no existe o ha sido eliminada.'));
        expect($messages['call_phase_id.required'])->toBe(__('Debe seleccionar una fase de la convocatoria.'));
        expect($messages['type.required'])->toBe(__('Debe seleccionar un tipo de resolución.'));
        expect($messages['title.required'])->toBe(__('El título de la resolución es obligatorio.'));
    });
});
