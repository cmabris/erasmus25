<?php

use App\Http\Requests\UpdateAcademicYearRequest;
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

describe('UpdateAcademicYearRequest - Authorization', function () {
    it('authorizes admin user to update academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            [
                'year' => '2025-2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-06-30',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user to update academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            [
                'year' => '2025-2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-06-30',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('denies editor user without admin role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            [
                'year' => '2025-2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-06-30',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            [
                'year' => '2025-2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-06-30',
            ]
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is not AcademicYear instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateAcademicYearRequest::create(
            '/admin/anios-academicos/1',
            'PUT',
            [
                'year' => '2025-2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-06-30',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', 'not-an-academic-year-instance');

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is null', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateAcademicYearRequest::create(
            '/admin/anios-academicos/1',
            'PUT',
            [
                'year' => '2025-2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-06-30',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', null);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });
});

describe('UpdateAcademicYearRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('year'))->toBeTrue();
        expect($validator->errors()->has('start_date'))->toBeTrue();
        expect($validator->errors()->has('end_date'))->toBeTrue();
    });

    it('validates year format with regex YYYY-YYYY', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });
        $rules = $request->rules();

        $invalidYears = [
            '2025',
            '2025-26',
            '2025-202',
            '2025-20256',
            '2025/2026',
            '2025_2026',
            'invalid',
        ];

        foreach ($invalidYears as $invalidYear) {
            $validator = Validator::make([
                'year' => $invalidYear,
                'start_date' => '2025-09-01',
                'end_date' => '2026-06-30',
            ], $rules);

            expect($validator->fails())->toBeTrue("Should fail for year: {$invalidYear}");
            expect($validator->errors()->has('year'))->toBeTrue();
        }
    });

    it('validates year format accepts valid YYYY-YYYY format', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });
        $rules = $request->rules();

        $validYears = [
            '2024-2025',
            '2025-2026',
            '2026-2027',
        ];

        foreach ($validYears as $validYear) {
            $validator = Validator::make([
                'year' => $validYear,
                'start_date' => '2025-09-01',
                'end_date' => '2026-06-30',
            ], $rules);

            expect($validator->passes())->toBeTrue("Should pass for year: {$validYear}");
        }
    });

    it('validates year uniqueness ignoring current academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear1 = AcademicYear::factory()->create(['year' => '2024-2025']);
        $academicYear2 = AcademicYear::factory()->create(['year' => '2025-2026']);

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear1->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear1) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear1);

            return $route;
        });
        $rules = $request->rules();

        // Should fail when trying to use year from another academic year
        $validator = Validator::make([
            'year' => '2025-2026', // Year from academicYear2
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('year'))->toBeTrue();
    });

    it('allows same year for the same academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });
        $rules = $request->rules();

        // Should pass when keeping the same year
        $validator = Validator::make([
            'year' => '2024-2025', // Same year as the academic year being updated
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
        ], $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('handles route model binding with AcademicYear instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });
        $rules = $request->rules();

        // Should work correctly with AcademicYear instance
        $validator = Validator::make([
            'year' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
        ], $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('validates start_date is required and valid date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });
        $rules = $request->rules();

        $validator = Validator::make([
            'year' => '2025-2026',
            'end_date' => '2026-06-30',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('start_date'))->toBeTrue();

        // Test invalid date format
        $validator2 = Validator::make([
            'year' => '2025-2026',
            'start_date' => 'invalid-date',
            'end_date' => '2026-06-30',
        ], $rules);

        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->has('start_date'))->toBeTrue();
    });

    it('validates end_date is required and valid date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });
        $rules = $request->rules();

        $validator = Validator::make([
            'year' => '2025-2026',
            'start_date' => '2025-09-01',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('end_date'))->toBeTrue();

        // Test invalid date format
        $validator2 = Validator::make([
            'year' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => 'invalid-date',
        ], $rules);

        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->has('end_date'))->toBeTrue();
    });

    it('validates end_date is after start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });
        $rules = $request->rules();

        $validator = Validator::make([
            'year' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2025-08-01', // Before start_date
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('end_date'))->toBeTrue();

        // Test same date (should fail)
        $validator2 = Validator::make([
            'year' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2025-09-01', // Same as start_date
        ], $rules);

        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->has('end_date'))->toBeTrue();
    });

    it('validates end_date can be after start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });
        $rules = $request->rules();

        $validator = Validator::make([
            'year' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30', // After start_date
        ], $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('validates is_current is optional and boolean', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });
        $rules = $request->rules();

        // Should pass without is_current
        $validator = Validator::make([
            'year' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
        ], $rules);

        expect($validator->passes())->toBeTrue();

        // Should pass with boolean true
        $validator2 = Validator::make([
            'year' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_current' => true,
        ], $rules);

        expect($validator2->passes())->toBeTrue();

        // Should pass with boolean false
        $validator3 = Validator::make([
            'year' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_current' => false,
        ], $rules);

        expect($validator3->passes())->toBeTrue();

        // Should fail with non-boolean value
        $validator4 = Validator::make([
            'year' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_current' => 'not-boolean',
        ], $rules);

        expect($validator4->fails())->toBeTrue();
        expect($validator4->errors()->has('is_current'))->toBeTrue();
    });
});

describe('UpdateAcademicYearRequest - Custom Messages', function () {
    it('has custom error messages for all fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });
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

        $academicYear = AcademicYear::factory()->create();

        $request = UpdateAcademicYearRequest::create(
            "/admin/anios-academicos/{$academicYear->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($academicYear) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/anios-academicos/{academic_year}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('academic_year', $academicYear);

            return $route;
        });
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
});
