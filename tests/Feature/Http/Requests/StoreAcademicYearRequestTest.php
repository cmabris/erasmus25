<?php

use App\Http\Requests\StoreAcademicYearRequest;
use App\Models\AcademicYear;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear roles
    Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);
});

describe('StoreAcademicYearRequest - Authorization', function () {
    it('authorizes admin user to create academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create(
            '/admin/anios-academicos',
            'POST',
            [
                'year' => '2024-2025',
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user to create academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create(
            '/admin/anios-academicos',
            'POST',
            [
                'year' => '2024-2025',
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('denies editor user without admin role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create(
            '/admin/anios-academicos',
            'POST',
            [
                'year' => '2024-2025',
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeFalse();
    });

    it('denies viewer user without admin role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create(
            '/admin/anios-academicos',
            'POST',
            [
                'year' => '2024-2025',
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $request = StoreAcademicYearRequest::create(
            '/admin/anios-academicos',
            'POST',
            [
                'year' => '2024-2025',
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
            ]
        );
        $request->setUserResolver(fn () => null);

        expect($request->authorize())->toBeFalse();
    });
});

describe('StoreAcademicYearRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create('/admin/anios-academicos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('year'))->toBeTrue();
        expect($validator->errors()->has('start_date'))->toBeTrue();
        expect($validator->errors()->has('end_date'))->toBeTrue();
    });

    it('validates year format with regex', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create('/admin/anios-academicos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'year' => '2024-25', // Formato incorrecto
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('year'))->toBeTrue();
    });

    it('validates year uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        AcademicYear::factory()->create(['year' => '2024-2025']);

        $request = StoreAcademicYearRequest::create('/admin/anios-academicos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'year' => '2024-2025', // Ya existe
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('year'))->toBeTrue();
    });

    it('validates start_date is a valid date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create('/admin/anios-academicos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'year' => '2024-2025',
            'start_date' => 'invalid-date',
            'end_date' => '2025-06-30',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('start_date'))->toBeTrue();
    });

    it('validates end_date is a valid date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create('/admin/anios-academicos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'year' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => 'invalid-date',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('end_date'))->toBeTrue();
    });

    it('validates end_date is after start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create('/admin/anios-academicos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'year' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2024-08-01', // Antes de start_date
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('end_date'))->toBeTrue();
    });

    it('validates is_current is boolean when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create('/admin/anios-academicos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'year' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_current' => 'not-a-boolean',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('is_current'))->toBeTrue();
    });

    it('accepts valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create('/admin/anios-academicos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'year' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_current' => true,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('StoreAcademicYearRequest - Custom Messages', function () {
    it('has custom error messages for all validation rules', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create('/admin/anios-academicos', 'POST', []);
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('year.required');
        expect($messages)->toHaveKey('year.regex');
        expect($messages)->toHaveKey('year.unique');
        expect($messages)->toHaveKey('start_date.required');
        expect($messages)->toHaveKey('start_date.date');
        expect($messages)->toHaveKey('end_date.required');
        expect($messages)->toHaveKey('end_date.date');
        expect($messages)->toHaveKey('end_date.after');
        expect($messages)->toHaveKey('is_current.boolean');
    });

    it('returns translated custom messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create('/admin/anios-academicos', 'POST', []);
        $messages = $request->messages();

        expect($messages['year.required'])->toBe(__('El año académico es obligatorio.'));
        expect($messages['year.regex'])->toBe(__('El formato del año académico debe ser YYYY-YYYY (ejemplo: 2024-2025).'));
        expect($messages['year.unique'])->toBe(__('Este año académico ya está registrado.'));
        expect($messages['start_date.required'])->toBe(__('La fecha de inicio es obligatoria.'));
        expect($messages['start_date.date'])->toBe(__('La fecha de inicio debe ser una fecha válida.'));
        expect($messages['end_date.required'])->toBe(__('La fecha de fin es obligatoria.'));
        expect($messages['end_date.date'])->toBe(__('La fecha de fin debe ser una fecha válida.'));
        expect($messages['end_date.after'])->toBe(__('La fecha de fin debe ser posterior a la fecha de inicio.'));
        expect($messages['is_current.boolean'])->toBe(__('El campo año actual debe ser verdadero o falso.'));
    });

    it('uses custom messages in validation errors', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreAcademicYearRequest::create('/admin/anios-academicos', 'POST', []);
        $rules = $request->rules();
        $messages = $request->messages();

        $validator = Validator::make([], $rules, $messages);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('year'))->toBe(__('El año académico es obligatorio.'));
        expect($validator->errors()->first('start_date'))->toBe(__('La fecha de inicio es obligatoria.'));
        expect($validator->errors()->first('end_date'))->toBe(__('La fecha de fin es obligatoria.'));
    });
});
