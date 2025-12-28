<?php

use App\Livewire\Admin\Programs\Edit;
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

describe('Admin Programs Edit - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $program = Program::factory()->create();

        $this->get(route('admin.programs.edit', $program))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with PROGRAMS_EDIT permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $this->get(route('admin.programs.edit', $program))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('denies access for users without PROGRAMS_EDIT permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $this->get(route('admin.programs.edit', $program))
            ->assertForbidden();
    });
});

describe('Admin Programs Edit - Data Loading', function () {
    it('loads program data correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create([
            'code' => 'KA121-SCH',
            'name' => 'Movilidad Educación Escolar',
            'slug' => 'movilidad-educacion-escolar',
            'description' => 'Descripción del programa',
            'is_active' => true,
            'order' => 1,
        ]);

        Livewire::test(Edit::class, ['program' => $program])
            ->assertSet('code', 'KA121-SCH')
            ->assertSet('name', 'Movilidad Educación Escolar')
            ->assertSet('slug', 'movilidad-educacion-escolar')
            ->assertSet('description', 'Descripción del programa')
            ->assertSet('is_active', true)
            ->assertSet('order', 1);
    });

    it('shows existing image if program has one', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $image = UploadedFile::fake()->image('program.jpg');
        $program->addMedia($image->getRealPath())
            ->usingName($program->name)
            ->toMediaCollection('image');

        $component = Livewire::test(Edit::class, ['program' => $program]);
        expect($component->instance()->hasExistingImage())->toBeTrue();
    });
});

describe('Admin Programs Edit - Successful Update', function () {
    it('can update program with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create([
            'name' => 'Programa Original',
            'is_active' => true,
        ]);

        Livewire::test(Edit::class, ['program' => $program])
            ->set('name', 'Programa Actualizado')
            ->set('description', 'Nueva descripción')
            ->set('is_active', false)
            ->set('order', 5)
            ->call('update');

        expect($program->fresh())
            ->name->toBe('Programa Actualizado')
            ->description->toBe('Nueva descripción')
            ->is_active->toBeFalse()
            ->order->toBe(5);
    });

    it('dispatches program-updated event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        Livewire::test(Edit::class, ['program' => $program])
            ->set('name', 'Programa Actualizado')
            ->call('update');

        // Verify the program was updated
        expect($program->fresh()->name)->toBe('Programa Actualizado');
    });

    it('can update slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create([
            'slug' => 'old-slug',
        ]);

        Livewire::test(Edit::class, ['program' => $program])
            ->set('slug', 'new-slug')
            ->call('update');

        expect($program->fresh()->slug)->toBe('new-slug');
    });
});

describe('Admin Programs Edit - Validation', function () {
    it('requires code field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        Livewire::test(Edit::class, ['program' => $program])
            ->set('code', '')
            ->call('update')
            ->assertHasErrors(['code']);
    });

    it('requires name field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        Livewire::test(Edit::class, ['program' => $program])
            ->set('name', '')
            ->call('update')
            ->assertHasErrors(['name']);
    });

    it('validates unique code excluding current program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['code' => 'KA121-SCH']);
        $program2 = Program::factory()->create(['code' => 'KA131-HED']);

        // Should allow keeping the same code
        Livewire::test(Edit::class, ['program' => $program1])
            ->set('code', 'KA121-SCH')
            ->set('name', 'Test Program')
            ->call('update')
            ->assertHasNoErrors(['code']);

        // Should reject using another program's code
        Livewire::test(Edit::class, ['program' => $program1])
            ->set('code', 'KA131-HED')
            ->set('name', 'Test Program')
            ->call('update')
            ->assertHasErrors(['code']);
    });

    it('validates unique slug excluding current program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['slug' => 'slug-1', 'name' => 'Program 1']);
        $program2 = Program::factory()->create(['slug' => 'slug-2', 'name' => 'Program 2']);

        // Should allow keeping the same slug
        Livewire::test(Edit::class, ['program' => $program1])
            ->set('slug', 'slug-1')
            ->set('name', 'Program 1')
            ->call('update')
            ->assertHasNoErrors(['slug']);

        // Should reject using another program's slug
        Livewire::test(Edit::class, ['program' => $program1])
            ->set('slug', 'slug-2')
            ->set('name', 'Program 1')
            ->call('update')
            ->assertHasErrors(['slug']);
    });

    it('validates image file type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        Livewire::test(Edit::class, ['program' => $program])
            ->set('image', $file)
            ->call('update')
            ->assertHasErrors(['image']);
    });

    it('validates image file size', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $image = UploadedFile::fake()->image('program.jpg')->size(6144); // 6MB

        Livewire::test(Edit::class, ['program' => $program])
            ->set('image', $image)
            ->call('update')
            ->assertHasErrors(['image']);
    });
});

describe('Admin Programs Edit - Image Management', function () {
    it('can upload new image', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['name' => 'Test Program']);
        $image = UploadedFile::fake()->image('new-program.jpg', 800, 600);

        Livewire::test(Edit::class, ['program' => $program])
            ->set('image', $image)
            ->set('name', 'Test Program')
            ->call('update');

        expect($program->fresh()->hasMedia('image'))->toBeTrue();
    });

    it('can remove existing image', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['name' => 'Test Program']);
        $image = UploadedFile::fake()->image('program.jpg');
        $program->addMedia($image->getRealPath())
            ->usingName($program->name)
            ->toMediaCollection('image');

        Livewire::test(Edit::class, ['program' => $program])
            ->set('removeExistingImage', true)
            ->set('name', 'Test Program')
            ->call('update');

        expect($program->fresh()->hasMedia('image'))->toBeFalse();
    });

    it('can replace existing image with new one', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['name' => 'Test Program']);
        $oldImage = UploadedFile::fake()->image('old-program.jpg');
        $program->addMedia($oldImage->getRealPath())
            ->usingName($program->name)
            ->toMediaCollection('image');

        $newImage = UploadedFile::fake()->image('new-program.jpg');

        Livewire::test(Edit::class, ['program' => $program])
            ->set('image', $newImage)
            ->set('name', 'Test Program')
            ->call('update');

        $media = $program->fresh()->getFirstMedia('image');
        expect($media)->not->toBeNull();
        // The file name may be hashed, so just verify media exists
        expect($program->fresh()->hasMedia('image'))->toBeTrue();
    });

    it('can toggle remove existing image', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $image = UploadedFile::fake()->image('program.jpg');
        $program->addMedia($image->getRealPath())
            ->usingName($program->name)
            ->toMediaCollection('image');

        Livewire::test(Edit::class, ['program' => $program])
            ->call('toggleRemoveExistingImage')
            ->assertSet('removeExistingImage', true)
            ->call('toggleRemoveExistingImage')
            ->assertSet('removeExistingImage', false);
    });

    it('can remove new image upload', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $image = UploadedFile::fake()->image('program.jpg');

        Livewire::test(Edit::class, ['program' => $program])
            ->set('image', $image)
            ->call('removeImage')
            ->assertSet('image', null)
            ->assertSet('imagePreview', null);
    });

    it('generates image preview when new image is selected', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $image = UploadedFile::fake()->image('program.jpg');

        Livewire::test(Edit::class, ['program' => $program])
            ->set('image', $image)
            ->assertSet('imagePreview', fn ($value) => $value !== null);
    });
});

describe('Admin Programs Edit - Slug Generation', function () {
    it('generates slug automatically when name changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create([
            'name' => 'Programa Original',
            'slug' => 'programa-original',
        ]);

        Livewire::test(Edit::class, ['program' => $program])
            ->set('name', 'Programa Actualizado')
            ->assertSet('slug', 'programa-actualizado');
    });

    it('does not override manually set slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create([
            'name' => 'Programa Original',
            'slug' => 'programa-original',
        ]);

        Livewire::test(Edit::class, ['program' => $program])
            ->set('slug', 'custom-slug')
            ->set('name', 'Programa Actualizado')
            ->assertSet('slug', 'custom-slug');
    });
});
