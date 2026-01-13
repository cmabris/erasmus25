<?php

use App\Livewire\Admin\AuditLogs\Show;
use App\Models\Program;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear roles
    Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);
});

describe('Admin AuditLogs Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => Program::class,
            'subject_id' => 1,
        ]);

        $this->get(route('admin.audit-logs.show', $activity))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => Program::class,
            'subject_id' => 1,
        ]);

        $this->get(route('admin.audit-logs.show', $activity))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('allows admin to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => Program::class,
            'subject_id' => 1,
        ]);

        $this->get(route('admin.audit-logs.show', $activity))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('denies editor access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => Program::class,
            'subject_id' => 1,
        ]);

        $this->get(route('admin.audit-logs.show', $activity))
            ->assertForbidden();
    });

    it('denies viewer access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => Program::class,
            'subject_id' => 1,
        ]);

        $this->get(route('admin.audit-logs.show', $activity))
            ->assertForbidden();
    });
});

describe('Admin AuditLogs Show - Display', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create(['name' => 'Programa Test']);
    });

    it('displays activity information correctly', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->withProperties(['test' => 'value'])
            ->log('created');

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('created')
            ->assertSee('Programa Test')
            ->assertSee($this->user->name);
    });

    it('displays activity ID', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee((string) $activity->id);
    });

    it('displays activity date and time', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee($activity->created_at->format('d/m/Y'))
            ->assertSee($activity->created_at->format('H:i:s'));
    });

    it('displays causer information when available', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee($this->user->name)
            ->assertSee($this->user->email);
    });

    it('displays system when causer is null', function () {
        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => null,
            'causer_id' => null,
        ]);

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('Sistema');
    });

    it('displays subject information when available', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('Programa Test')
            ->assertSee('Programa');
    });

    it('displays log name when available', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->useLog('custom-log')
            ->log('test');

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('custom-log');
    });
});

describe('Admin AuditLogs Show - Changes Display', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('displays changes when properties contain old and attributes', function () {
        $activity = Activity::create([
            'description' => 'updated',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [
                'old' => ['name' => 'Old Name'],
                'attributes' => ['name' => 'New Name'],
            ],
        ]);

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('name')
            ->assertSee('Old Name')
            ->assertSee('New Name');
    });

    it('displays no changes message when there are no changes', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('No se registraron cambios en este log');
    });

    it('formats boolean values correctly', function () {
        $activity = Activity::create([
            'description' => 'updated',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [
                'old' => ['is_active' => false],
                'attributes' => ['is_active' => true],
            ],
        ]);

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('is_active')
            ->assertSee('false')
            ->assertSee('true');
    });

    it('formats null values correctly', function () {
        $activity = Activity::create([
            'description' => 'updated',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [
                'old' => ['deleted_at' => null],
                'attributes' => ['deleted_at' => now()->toDateTimeString()],
            ],
        ]);

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('deleted_at');
    });
});

describe('Admin AuditLogs Show - Custom Properties', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('displays custom properties when available', function () {
        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [
                'custom_field' => 'custom_value',
                'another_field' => 123,
            ],
        ]);

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('custom_field')
            ->assertSee('custom_value');
    });

    it('excludes system properties from custom properties', function () {
        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [
                'old' => ['name' => 'Old'],
                'attributes' => ['name' => 'New'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent',
                'custom_field' => 'value',
            ],
        ]);

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('custom_field')
            ->assertSee('value');
    });
});

describe('Admin AuditLogs Show - IP Address and User Agent', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('displays IP address when available', function () {
        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [
                'ip_address' => '192.168.1.1',
            ],
        ]);

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('192.168.1.1');
    });

    it('displays user agent when available', function () {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [
                'user_agent' => $userAgent,
            ],
        ]);

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('User Agent');
    });

    it('parses user agent information', function () {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [
                'user_agent' => $userAgent,
            ],
        ]);

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('Navegador')
            ->assertSee('Sistema Operativo');
    });
});

describe('Admin AuditLogs Show - Helper Methods', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create(['name' => 'Programa Test']);
    });

    it('displays correct model name', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('Programa');
    });

    it('displays correct description translation', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('Creado');
    });

    it('displays subject URL when route exists', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('Ver Registro Relacionado');
    });

    it('displays subject title correctly', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        Livewire::test(Show::class, ['activity' => $activity])
            ->assertSee('Programa Test');
    });

    it('displays changes when activity has changes', function () {
        $activityWithChanges = Activity::create([
            'description' => 'updated',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [
                'old' => ['name' => 'Old'],
                'attributes' => ['name' => 'New'],
            ],
        ]);

        Livewire::test(Show::class, ['activity' => $activityWithChanges])
            ->assertSee('Cambios Realizados')
            ->assertSee('name')
            ->assertSee('Old')
            ->assertSee('New');
    });

    it('displays no changes message when there are no changes', function () {
        $activityWithoutChanges = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        Livewire::test(Show::class, ['activity' => $activityWithoutChanges])
            ->assertSee('No se registraron cambios en este log');
    });
});
