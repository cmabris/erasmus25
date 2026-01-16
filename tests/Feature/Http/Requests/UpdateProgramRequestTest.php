<?php

use App\Http\Requests\UpdateProgramRequest;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear los permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
        Permissions::PROGRAMS_CREATE,
        Permissions::PROGRAMS_EDIT,
        Permissions::PROGRAMS_DELETE,
    ]);

    // Editor tiene permisos limitados (sin edit)
    $editor->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
    ]);
});

describe('UpdateProgramRequest - Authorization', function () {
    it('authorizes user with edit permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            [
                'code' => 'TEST-001',
                'name' => 'Test Program',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            [
                'code' => 'TEST-001',
                'name' => 'Test Program',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('denies user without edit permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            [
                'code' => 'TEST-001',
                'name' => 'Test Program',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            [
                'code' => 'TEST-001',
                'name' => 'Test Program',
            ]
        );
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is not Program instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateProgramRequest::create(
            '/admin/programas/1',
            'PUT',
            [
                'code' => 'TEST-001',
                'name' => 'Test Program',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', 'not-a-program-instance');

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is null', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateProgramRequest::create(
            '/admin/programas/1',
            'PUT',
            [
                'code' => 'TEST-001',
                'name' => 'Test Program',
            ]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', null);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });
});

describe('UpdateProgramRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('code'))->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates code is required, string, and max 255', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });
        $rules = $request->rules();

        // Test max length
        $validator = Validator::make([
            'code' => str_repeat('a', 256), // Exceeds max 255
            'name' => 'Test Program',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('code'))->toBeTrue();
    });

    it('validates code uniqueness ignoring current program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['code' => 'PROG-001']);
        $program2 = Program::factory()->create(['code' => 'PROG-002']);

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program1->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program1) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program1);

            return $route;
        });
        $rules = $request->rules();

        // Should fail when trying to use code from another program
        $validator = Validator::make([
            'code' => 'PROG-002', // Code from program2
            'name' => 'Test Program',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('code'))->toBeTrue();
    });

    it('allows same code for the same program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['code' => 'PROG-001']);

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });
        $rules = $request->rules();

        // Should pass when keeping the same code
        $validator = Validator::make([
            'code' => 'PROG-001', // Same code as the program being updated
            'name' => 'Test Program',
        ], $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('validates name is required, string, and max 255', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });
        $rules = $request->rules();

        // Test max length
        $validator = Validator::make([
            'code' => 'TEST-001',
            'name' => str_repeat('a', 256), // Exceeds max 255
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates slug is optional, string, max 255, and unique ignoring current program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['slug' => 'program-1']);
        $program2 = Program::factory()->create(['slug' => 'program-2']);

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program1->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program1) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program1);

            return $route;
        });
        $rules = $request->rules();

        // Should pass without slug
        $validator = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
        ], $rules);

        expect($validator->passes())->toBeTrue();

        // Should fail when trying to use slug from another program
        $validator2 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'slug' => 'program-2', // Slug from program2
        ], $rules);

        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->has('slug'))->toBeTrue();

        // Should pass when keeping the same slug
        $validator3 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'slug' => 'program-1', // Same slug as the program being updated
        ], $rules);

        expect($validator3->passes())->toBeTrue();
    });

    it('validates description is optional and string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });
        $rules = $request->rules();

        // Should pass without description
        $validator = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
        ], $rules);

        expect($validator->passes())->toBeTrue();

        // Should pass with description
        $validator2 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'description' => 'Test description',
        ], $rules);

        expect($validator2->passes())->toBeTrue();
    });

    it('validates is_active is optional and boolean', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });
        $rules = $request->rules();

        // Should pass without is_active
        $validator = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
        ], $rules);

        expect($validator->passes())->toBeTrue();

        // Should pass with boolean true
        $validator2 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'is_active' => true,
        ], $rules);

        expect($validator2->passes())->toBeTrue();

        // Should pass with boolean false
        $validator3 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'is_active' => false,
        ], $rules);

        expect($validator3->passes())->toBeTrue();

        // Should fail with non-boolean value
        $validator4 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'is_active' => 'not-boolean',
        ], $rules);

        expect($validator4->fails())->toBeTrue();
        expect($validator4->errors()->has('is_active'))->toBeTrue();
    });

    it('validates order is optional and integer', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });
        $rules = $request->rules();

        // Should pass without order
        $validator = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
        ], $rules);

        expect($validator->passes())->toBeTrue();

        // Should pass with integer
        $validator2 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'order' => 1,
        ], $rules);

        expect($validator2->passes())->toBeTrue();

        // Should fail with non-integer value
        $validator3 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'order' => 'not-integer',
        ], $rules);

        expect($validator3->fails())->toBeTrue();
        expect($validator3->errors()->has('order'))->toBeTrue();
    });

    it('validates image is optional, image file, valid mimes, and max 5MB', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });
        $rules = $request->rules();

        // Should pass without image
        $validator = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
        ], $rules);

        expect($validator->passes())->toBeTrue();

        // Should pass with valid image (JPEG)
        $jpegFile = UploadedFile::fake()->image('test.jpg', 100, 100);
        $validator2 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'image' => $jpegFile,
        ], $rules);

        expect($validator2->passes())->toBeTrue();

        // Should pass with valid image (PNG)
        $pngFile = UploadedFile::fake()->image('test.png', 100, 100);
        $validator3 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'image' => $pngFile,
        ], $rules);

        expect($validator3->passes())->toBeTrue();

        // Should pass with valid image (WebP)
        $webpFile = UploadedFile::fake()->image('test.webp', 100, 100);
        $validator4 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'image' => $webpFile,
        ], $rules);

        expect($validator4->passes())->toBeTrue();

        // Should fail with non-image file
        $pdfFile = UploadedFile::fake()->create('test.pdf', 100);
        $validator5 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'image' => $pdfFile,
        ], $rules);

        expect($validator5->fails())->toBeTrue();
        expect($validator5->errors()->has('image'))->toBeTrue();

        // Should fail with invalid mime type
        $txtFile = UploadedFile::fake()->create('test.txt', 100);
        $validator6 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'image' => $txtFile,
        ], $rules);

        expect($validator6->fails())->toBeTrue();
        expect($validator6->errors()->has('image'))->toBeTrue();

        // Should fail with file exceeding 5MB (5120 KB)
        $largeFile = UploadedFile::fake()->image('large.jpg', 100, 100)->size(5121); // 5121 KB = 5.001 MB
        $validator7 = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'image' => $largeFile,
        ], $rules);

        expect($validator7->fails())->toBeTrue();
        expect($validator7->errors()->has('image'))->toBeTrue();
    });

    it('handles route model binding with Program instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['code' => 'PROG-001']);

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });
        $rules = $request->rules();

        // Should work correctly with Program instance
        $validator = Validator::make([
            'code' => 'PROG-001',
            'name' => 'Test Program',
        ], $rules);

        expect($validator->passes())->toBeTrue();
    });
});

describe('UpdateProgramRequest - Custom Messages', function () {
    it('has custom error messages for all fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('code.required');
        expect($messages)->toHaveKey('code.unique');
        expect($messages)->toHaveKey('name.required');
        expect($messages)->toHaveKey('slug.unique');
        expect($messages)->toHaveKey('image.image');
        expect($messages)->toHaveKey('image.mimes');
        expect($messages)->toHaveKey('image.max');
    });

    it('returns translated custom messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $request = UpdateProgramRequest::create(
            "/admin/programas/{$program->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($program) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/programas/{program}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('program', $program);

            return $route;
        });
        $messages = $request->messages();

        expect($messages['code.required'])->toBe(__('El código del programa es obligatorio.'));
        expect($messages['code.unique'])->toBe(__('Este código ya está en uso.'));
        expect($messages['name.required'])->toBe(__('El nombre del programa es obligatorio.'));
        expect($messages['slug.unique'])->toBe(__('Este slug ya está en uso.'));
        expect($messages['image.image'])->toBe(__('El archivo debe ser una imagen.'));
        expect($messages['image.mimes'])->toBe(__('La imagen debe ser JPEG, PNG, WebP o GIF.'));
        expect($messages['image.max'])->toBe(__('La imagen no puede ser mayor de 5MB.'));
    });
});
