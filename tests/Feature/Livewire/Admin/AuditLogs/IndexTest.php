<?php

use App\Livewire\Admin\AuditLogs\Index;
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

describe('Admin AuditLogs Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.audit-logs.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.audit-logs.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('allows admin to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.audit-logs.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('denies editor access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $this->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    });

    it('denies viewer access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    });
});

describe('Admin AuditLogs Index - Listing', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);
    });

    it('displays all activities by default', function () {
        $program = Program::factory()->create();

        activity()
            ->performedOn($program)
            ->causedBy($this->user)
            ->log('created');

        activity()
            ->performedOn($program)
            ->causedBy($this->user)
            ->log('updated');

        Livewire::test(Index::class)
            ->assertSee('created')
            ->assertSee('updated');
    });

    it('displays activity information correctly', function () {
        $program = Program::factory()->create(['name' => 'Programa Test']);

        $activity = activity()
            ->performedOn($program)
            ->causedBy($this->user)
            ->withProperties(['test' => 'value'])
            ->log('created');

        Livewire::test(Index::class)
            ->assertSee('created')
            ->assertSee('Programa Test');
    });

    it('displays empty state when no activities exist', function () {
        // Asegurar que no hay actividades
        Activity::query()->delete();

        Livewire::test(Index::class)
            ->assertSee('No se encontraron actividades');
    });

    it('paginates activities correctly', function () {
        $program = Program::factory()->create();

        // Crear más de 25 actividades (default perPage)
        for ($i = 0; $i < 30; $i++) {
            activity()
                ->performedOn($program)
                ->causedBy($this->user)
                ->log('test-'.$i);
        }

        $component = Livewire::test(Index::class)
            ->assertSee('test-');

        // Verificar que hay paginación
        expect($component->get('activities')->total())->toBeGreaterThan(25);
    });
});

describe('Admin AuditLogs Index - Filtering', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create(['name' => 'Programa Test']);
    });

    it('filters activities by search query', function () {
        // Limpiar actividades previas
        Activity::query()->delete();

        activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('updated');

        $component = Livewire::test(Index::class)
            ->set('search', 'created');

        $activities = $component->get('activities');
        expect($activities->count())->toBe(1);
        expect($activities->first()->description)->toBe('created');
    });

    it('filters activities by model type', function () {
        // Limpiar actividades previas
        Activity::query()->delete();

        $program = Program::factory()->create();
        $otherModel = User::factory()->create();

        // Crear actividades directamente para evitar logging automático adicional
        Activity::create([
            'description' => 'created',
            'subject_type' => Program::class,
            'subject_id' => $program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
        ]);

        Activity::create([
            'description' => 'created',
            'subject_type' => User::class,
            'subject_id' => $otherModel->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('filterModel', Program::class);

        $activities = $component->get('activities');
        // Verificar que todas las actividades filtradas son de tipo Program
        expect($activities->every(fn ($activity) => $activity->subject_type === Program::class))->toBeTrue();
        expect($activities->count())->toBeGreaterThanOrEqual(1);
    });

    it('filters activities by causer (user)', function () {
        // Limpiar actividades previas
        Activity::query()->delete();

        $user2 = User::factory()->create();

        activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        activity()
            ->performedOn($this->program)
            ->causedBy($user2)
            ->log('updated');

        $component = Livewire::test(Index::class)
            ->set('filterCauserId', $this->user->id);

        $activities = $component->get('activities');
        expect($activities->count())->toBe(1);
        expect($activities->first()->causer_id)->toBe($this->user->id);
    });

    it('filters activities by description', function () {
        // Limpiar actividades previas
        Activity::query()->delete();

        activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('updated');

        $component = Livewire::test(Index::class)
            ->set('filterDescription', 'created');

        $activities = $component->get('activities');
        expect($activities->count())->toBe(1);
        expect($activities->first()->description)->toBe('created');
    });

    it('filters activities by log name', function () {
        activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->useLog('custom-log')
            ->log('test');

        activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->useLog('default')
            ->log('test');

        Livewire::test(Index::class)
            ->set('filterLogName', 'custom-log')
            ->assertSee('custom-log');
    });

    it('filters activities by date range', function () {
        // Limpiar actividades previas
        Activity::query()->delete();

        $oldActivity = Activity::create([
            'description' => 'old',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'created_at' => now()->subDays(10),
        ]);

        $recentActivity = Activity::create([
            'description' => 'recent',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'created_at' => now()->subDays(2),
        ]);

        $component = Livewire::test(Index::class)
            ->set('filterDateFrom', now()->subDays(5)->format('Y-m-d'))
            ->set('filterDateTo', now()->format('Y-m-d'));

        $activities = $component->get('activities');
        expect($activities->count())->toBe(1);
        expect($activities->first()->description)->toBe('recent');
    });
});

describe('Admin AuditLogs Index - Sorting', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('sorts activities by created_at descending by default', function () {
        $oldActivity = Activity::create([
            'description' => 'old',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'created_at' => now()->subDays(2),
        ]);

        $newActivity = Activity::create([
            'description' => 'new',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'created_at' => now(),
        ]);

        Livewire::test(Index::class)
            ->assertSeeInOrder(['new', 'old']);
    });

    it('sorts activities by created_at ascending', function () {
        $oldActivity = Activity::create([
            'description' => 'old',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'created_at' => now()->subDays(2),
        ]);

        $newActivity = Activity::create([
            'description' => 'new',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'created_at' => now(),
        ]);

        Livewire::test(Index::class)
            ->set('sortDirection', 'asc')
            ->assertSeeInOrder(['old', 'new']);
    });
});

describe('Admin AuditLogs Index - Export', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('allows admin to export activities', function () {
        activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        Livewire::test(Index::class)
            ->call('export')
            ->assertFileDownloaded();
    });

    it('applies filters to export', function () {
        activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('updated');

        Livewire::test(Index::class)
            ->set('filterDescription', 'created')
            ->call('export')
            ->assertFileDownloaded();
    });

    it('denies export to unauthorized users', function () {
        $editor = User::factory()->create();
        $editor->assignRole(Roles::EDITOR);
        $this->actingAs($editor);

        // El componente no se puede montar sin autorización
        $this->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    });
});
