<?php

use App\Http\Requests\StoreProgramRequest;
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

    // Editor tiene permisos limitados (sin create)
    $editor->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
        Permissions::PROGRAMS_EDIT,
    ]);
});

describe('StoreProgramRequest - Authorization', function () {
    it('authorizes user with create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreProgramRequest::create(
            '/admin/programas',
            'POST',
            [
                'code' => 'TEST-001',
                'name' => 'Test Program',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = StoreProgramRequest::create(
            '/admin/programas',
            'POST',
            [
                'code' => 'TEST-001',
                'name' => 'Test Program',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('denies editor user without create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $request = StoreProgramRequest::create(
            '/admin/programas',
            'POST',
            [
                'code' => 'TEST-001',
                'name' => 'Test Program',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeFalse();
    });

    it('denies viewer user without create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $request = StoreProgramRequest::create(
            '/admin/programas',
            'POST',
            [
                'code' => 'TEST-001',
                'name' => 'Test Program',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $request = StoreProgramRequest::create(
            '/admin/programas',
            'POST',
            [
                'code' => 'TEST-001',
                'name' => 'Test Program',
            ]
        );
        $request->setUserResolver(fn () => null);

        expect($request->authorize())->toBeFalse();
    });
});

describe('StoreProgramRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreProgramRequest::create('/admin/programas', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('code'))->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates code uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['code' => 'EXISTING-001']);

        $request = StoreProgramRequest::create('/admin/programas', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'code' => 'EXISTING-001', // Ya existe
            'name' => 'Test Program',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('code'))->toBeTrue();
    });

    it('validates slug uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['slug' => 'existing-slug']);

        $request = StoreProgramRequest::create('/admin/programas', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'slug' => 'existing-slug', // Ya existe
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('validates image file type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreProgramRequest::create('/admin/programas', 'POST', []);
        $rules = $request->rules();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $validator = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'image' => $file,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('image'))->toBeTrue();
    });

    it('validates image file size', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreProgramRequest::create('/admin/programas', 'POST', []);
        $rules = $request->rules();

        // Crear un archivo de más de 5MB (5120 KB)
        $file = UploadedFile::fake()->image('large.jpg')->size(6000); // 6MB

        $validator = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'image' => $file,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('image'))->toBeTrue();
    });

    it('validates is_active is boolean when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreProgramRequest::create('/admin/programas', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'is_active' => 'not-a-boolean',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('is_active'))->toBeTrue();
    });

    it('validates order is integer when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreProgramRequest::create('/admin/programas', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'order' => 'not-an-integer',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('order'))->toBeTrue();
    });

    it('accepts valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreProgramRequest::create('/admin/programas', 'POST', []);
        $rules = $request->rules();

        $file = UploadedFile::fake()->image('program.jpg');

        $validator = Validator::make([
            'code' => 'TEST-001',
            'name' => 'Test Program',
            'slug' => 'test-program',
            'description' => 'Test description',
            'is_active' => true,
            'order' => 1,
            'image' => $file,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('StoreProgramRequest - Custom Messages', function () {
    it('has custom error messages for all validation rules', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreProgramRequest::create('/admin/programas', 'POST', []);
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

        $request = StoreProgramRequest::create('/admin/programas', 'POST', []);
        $messages = $request->messages();

        expect($messages['code.required'])->toBe(__('El código del programa es obligatorio.'));
        expect($messages['code.unique'])->toBe(__('Este código ya está en uso.'));
        expect($messages['name.required'])->toBe(__('El nombre del programa es obligatorio.'));
        expect($messages['slug.unique'])->toBe(__('Este slug ya está en uso.'));
        expect($messages['image.image'])->toBe(__('El archivo debe ser una imagen.'));
        expect($messages['image.mimes'])->toBe(__('La imagen debe ser JPEG, PNG, WebP o GIF.'));
        expect($messages['image.max'])->toBe(__('La imagen no puede ser mayor de 5MB.'));
    });

    it('uses custom messages in validation errors', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreProgramRequest::create('/admin/programas', 'POST', []);
        $rules = $request->rules();
        $messages = $request->messages();

        $validator = Validator::make([], $rules, $messages);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('code'))->toBe(__('El código del programa es obligatorio.'));
        expect($validator->errors()->first('name'))->toBe(__('El nombre del programa es obligatorio.'));
    });
});
