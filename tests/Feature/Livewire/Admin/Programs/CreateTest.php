<?php

use App\Livewire\Admin\Programs\Create;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
        Permissions::PROGRAMS_CREATE,
        Permissions::PROGRAMS_EDIT,
        Permissions::PROGRAMS_DELETE,
    ]);
    $editor->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
        Permissions::PROGRAMS_CREATE,
        Permissions::PROGRAMS_EDIT,
    ]);
    $viewer->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
    ]);

    Storage::fake('public');
});

describe('Admin Programs Create - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.programs.create'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with PROGRAMS_CREATE permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.programs.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('denies access for users without PROGRAMS_CREATE permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.programs.create'))
            ->assertForbidden();
    });
});

describe('Admin Programs Create - Successful Creation', function () {
    it('can create a program with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('code', 'KA121-SCH')
            ->set('name', 'Movilidad Educación Escolar')
            ->set('description', 'Programa de movilidad escolar')
            ->set('is_active', true)
            ->set('order', 1)
            ->call('store')
            ->assertRedirect(route('admin.programs.index'));

        expect(Program::where('code', 'KA121-SCH')->exists())->toBeTrue();
    });

    it('generates slug automatically from name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('code', 'KA121-SCH')
            ->set('name', 'Movilidad Educación Escolar')
            ->call('store');

        $program = Program::where('code', 'KA121-SCH')->first();
        expect($program->slug)->toBe('movilidad-educacion-escolar');
    });

    it('uses provided slug if given', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('code', 'KA121-SCH')
            ->set('name', 'Movilidad Educación Escolar')
            ->set('slug', 'custom-slug')
            ->call('store');

        $program = Program::where('code', 'KA121-SCH')->first();
        expect($program->slug)->toBe('custom-slug');
    });

    it('creates program with image', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $image = UploadedFile::fake()->image('program.jpg', 800, 600);

        Livewire::test(Create::class)
            ->set('code', 'KA121-SCH')
            ->set('name', 'Movilidad Educación Escolar')
            ->set('image', $image)
            ->call('store');

        $program = Program::where('code', 'KA121-SCH')->first();
        expect($program->hasMedia('image'))->toBeTrue();
    });

    it('dispatches program-created event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('code', 'KA121-SCH')
            ->set('name', 'Movilidad Educación Escolar')
            ->call('store')
            ->assertDispatched('program-created');
    });
});

describe('Admin Programs Create - Validation', function () {
    it('requires code field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Movilidad Educación Escolar')
            ->call('store')
            ->assertHasErrors(['code']);
    });

    it('requires name field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('code', 'KA121-SCH')
            ->call('store')
            ->assertHasErrors(['name']);
    });

    it('validates unique code', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['code' => 'KA121-SCH']);

        Livewire::test(Create::class)
            ->set('code', 'KA121-SCH')
            ->set('name', 'Movilidad Educación Escolar')
            ->call('store')
            ->assertHasErrors(['code']);
    });

    it('validates unique slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['slug' => 'existing-slug']);

        Livewire::test(Create::class)
            ->set('code', 'KA121-SCH')
            ->set('name', 'Movilidad Educación Escolar')
            ->set('slug', 'existing-slug')
            ->call('store')
            ->assertHasErrors(['slug']);
    });

    it('validates image file type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        Livewire::test(Create::class)
            ->set('code', 'KA121-SCH')
            ->set('name', 'Movilidad Educación Escolar')
            ->set('image', $file)
            ->call('store')
            ->assertHasErrors(['image']);
    });

    it('validates image file size', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $image = UploadedFile::fake()->image('program.jpg')->size(6144); // 6MB

        Livewire::test(Create::class)
            ->set('code', 'KA121-SCH')
            ->set('name', 'Movilidad Educación Escolar')
            ->set('image', $image)
            ->call('store')
            ->assertHasErrors(['image']);
    });

    it('accepts valid image types', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $jpeg = UploadedFile::fake()->image('program.jpg');
        $png = UploadedFile::fake()->image('program.png');
        $webp = UploadedFile::fake()->image('program.webp');

        foreach ([$jpeg, $png, $webp] as $image) {
            Livewire::test(Create::class)
                ->set('code', 'KA121-SCH-'.uniqid())
                ->set('name', 'Movilidad Educación Escolar')
                ->set('image', $image)
                ->call('store')
                ->assertHasNoErrors(['image']);
        }
    });
});

describe('Admin Programs Create - Image Management', function () {
    it('generates image preview when image is selected', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $image = UploadedFile::fake()->image('program.jpg');

        Livewire::test(Create::class)
            ->set('image', $image)
            ->assertSet('imagePreview', fn ($value) => $value !== null);
    });

    it('can remove selected image', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $image = UploadedFile::fake()->image('program.jpg');

        Livewire::test(Create::class)
            ->set('image', $image)
            ->call('removeImage')
            ->assertSet('image', null)
            ->assertSet('imagePreview', null);
    });

    it('validates image on selection', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        Livewire::test(Create::class)
            ->set('image', $file)
            ->assertHasErrors(['image']);
    });
});

describe('Admin Programs Create - Slug Generation', function () {
    it('generates slug automatically when name changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Movilidad Educación Escolar')
            ->assertSet('slug', 'movilidad-educacion-escolar');
    });

    it('does not override manually set slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('slug', 'custom-slug')
            ->set('name', 'Movilidad Educación Escolar')
            ->assertSet('slug', 'custom-slug');
    });
});

describe('Admin Programs Create - Default Values', function () {
    it('sets default active status to true', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->assertSet('is_active', true);
    });

    it('sets default order to 0', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->assertSet('order', 0);
    });
});
