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

describe('Admin AuditLogs Index - Sort and Reset', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);
    });

    it('can sort by a field', function () {
        $component = Livewire::test(Index::class);

        // Default sort
        expect($component->get('sortField'))->toBe('created_at');
        expect($component->get('sortDirection'))->toBe('desc');

        // Sort by description (new field, should be asc)
        $component->call('sortBy', 'description');
        expect($component->get('sortField'))->toBe('description');
        expect($component->get('sortDirection'))->toBe('asc');
    });

    it('toggles sort direction when sorting by same field', function () {
        $component = Livewire::test(Index::class)
            ->set('sortField', 'description')
            ->set('sortDirection', 'asc');

        // Sort by same field - should toggle direction
        $component->call('sortBy', 'description');
        expect($component->get('sortDirection'))->toBe('desc');

        // Sort again - should toggle back
        $component->call('sortBy', 'description');
        expect($component->get('sortDirection'))->toBe('asc');
    });

    it('can reset all filters', function () {
        $component = Livewire::test(Index::class)
            ->set('search', 'test search')
            ->set('filterModel', Program::class)
            ->set('filterCauserId', 1)
            ->set('filterDescription', 'created')
            ->set('filterLogName', 'custom')
            ->set('filterDateFrom', '2024-01-01')
            ->set('filterDateTo', '2024-12-31');

        // Reset all filters
        $component->call('resetFilters');

        expect($component->get('search'))->toBe('')
            ->and($component->get('filterModel'))->toBeNull()
            ->and($component->get('filterCauserId'))->toBeNull()
            ->and($component->get('filterDescription'))->toBeNull()
            ->and($component->get('filterLogName'))->toBeNull()
            ->and($component->get('filterDateFrom'))->toBeNull()
            ->and($component->get('filterDateTo'))->toBeNull();
    });
});

describe('Admin AuditLogs Index - Helper Methods Coverage', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);
    });

    it('returns all model display names', function () {
        $component = Livewire::test(Index::class);
        $instance = $component->instance();

        expect($instance->getModelDisplayName('App\Models\Program'))->toBe(__('Programa'))
            ->and($instance->getModelDisplayName('App\Models\Call'))->toBe(__('Convocatoria'))
            ->and($instance->getModelDisplayName('App\Models\NewsPost'))->toBe(__('Noticia'))
            ->and($instance->getModelDisplayName('App\Models\Document'))->toBe(__('Documento'))
            ->and($instance->getModelDisplayName('App\Models\ErasmusEvent'))->toBe(__('Evento'))
            ->and($instance->getModelDisplayName('App\Models\AcademicYear'))->toBe(__('Año Académico'))
            ->and($instance->getModelDisplayName('App\Models\DocumentCategory'))->toBe(__('Categoría de Documento'))
            ->and($instance->getModelDisplayName('App\Models\NewsTag'))->toBe(__('Etiqueta de Noticia'))
            ->and($instance->getModelDisplayName('App\Models\CallPhase'))->toBe(__('Fase de Convocatoria'))
            ->and($instance->getModelDisplayName('App\Models\Resolution'))->toBe(__('Resolución'))
            ->and($instance->getModelDisplayName(null))->toBe('-')
            ->and($instance->getModelDisplayName('App\Models\Unknown'))->toBe('Unknown');
    });

    it('returns all description display names', function () {
        $component = Livewire::test(Index::class);
        $instance = $component->instance();

        expect($instance->getDescriptionDisplayName('created'))->toBe(__('Creado'))
            ->and($instance->getDescriptionDisplayName('updated'))->toBe(__('Actualizado'))
            ->and($instance->getDescriptionDisplayName('deleted'))->toBe(__('Eliminado'))
            ->and($instance->getDescriptionDisplayName('publish'))->toBe(__('Publicado'))
            ->and($instance->getDescriptionDisplayName('published'))->toBe(__('Publicado'))
            ->and($instance->getDescriptionDisplayName('archive'))->toBe(__('Archivado'))
            ->and($instance->getDescriptionDisplayName('archived'))->toBe(__('Archivado'))
            ->and($instance->getDescriptionDisplayName('restore'))->toBe(__('Restaurado'))
            ->and($instance->getDescriptionDisplayName('restored'))->toBe(__('Restaurado'))
            ->and($instance->getDescriptionDisplayName('custom_action'))->toBe('Custom_action');
    });

    it('returns all description badge variants', function () {
        $component = Livewire::test(Index::class);
        $instance = $component->instance();

        expect($instance->getDescriptionBadgeVariant('created'))->toBe('success')
            ->and($instance->getDescriptionBadgeVariant('publish'))->toBe('success')
            ->and($instance->getDescriptionBadgeVariant('published'))->toBe('success')
            ->and($instance->getDescriptionBadgeVariant('restore'))->toBe('success')
            ->and($instance->getDescriptionBadgeVariant('restored'))->toBe('success')
            ->and($instance->getDescriptionBadgeVariant('updated'))->toBe('info')
            ->and($instance->getDescriptionBadgeVariant('deleted'))->toBe('danger')
            ->and($instance->getDescriptionBadgeVariant('archive'))->toBe('danger')
            ->and($instance->getDescriptionBadgeVariant('archived'))->toBe('danger')
            ->and($instance->getDescriptionBadgeVariant('unknown'))->toBe('neutral');
    });

    it('returns null for getSubjectUrl with null params', function () {
        $component = Livewire::test(Index::class);
        $instance = $component->instance();

        expect($instance->getSubjectUrl(null, 1))->toBeNull()
            ->and($instance->getSubjectUrl('App\Models\Program', null))->toBeNull()
            ->and($instance->getSubjectUrl(null, null))->toBeNull();
    });

    it('returns null for getSubjectUrl with unknown model', function () {
        $component = Livewire::test(Index::class);
        expect($component->instance()->getSubjectUrl('App\Models\Unknown', 1))->toBeNull();
    });

    it('returns subject URL for mapped models', function () {
        $component = Livewire::test(Index::class);
        $instance = $component->instance();
        $program = Program::factory()->create();

        $url = $instance->getSubjectUrl('App\Models\Program', $program->id);
        expect($url)->toBe(route('admin.programs.show', $program->id));
    });

    it('returns dash for null subject in getSubjectTitle', function () {
        $component = Livewire::test(Index::class);
        expect($component->instance()->getSubjectTitle(null))->toBe('-');
    });

    it('returns subject title with title property', function () {
        $component = Livewire::test(Index::class);
        $subject = (object) ['title' => 'My Title', 'id' => 1];
        expect($component->instance()->getSubjectTitle($subject))->toBe('My Title');
    });

    it('returns subject title with name property when no title', function () {
        $component = Livewire::test(Index::class);
        $subject = (object) ['name' => 'My Name', 'id' => 1];
        expect($component->instance()->getSubjectTitle($subject))->toBe('My Name');
    });

    it('returns fallback subject title when no title or name', function () {
        $component = Livewire::test(Index::class);
        $subject = (object) ['id' => 42];
        expect($component->instance()->getSubjectTitle($subject))->toBe(__('Registro #:id', ['id' => 42]));
    });

    it('returns dash for null properties in formatChangesSummary', function () {
        $component = Livewire::test(Index::class);
        expect($component->instance()->formatChangesSummary(null))->toBe('-');
    });

    it('returns sin cambios when no actual changes in formatChangesSummary', function () {
        $component = Livewire::test(Index::class);
        $properties = [
            'old' => ['name' => 'Same'],
            'attributes' => ['name' => 'Same'],
        ];
        expect($component->instance()->formatChangesSummary($properties))->toBe(__('Sin cambios'));
    });

    it('formats changes summary with actual changes', function () {
        $component = Livewire::test(Index::class);
        $properties = [
            'old' => ['name' => 'Old Name', 'email' => 'old@example.com'],
            'attributes' => ['name' => 'New Name', 'email' => 'new@example.com'],
        ];
        $result = $component->instance()->formatChangesSummary($properties);
        expect($result)->toContain('name')
            ->and($result)->toContain('email');
    });

    it('formats changes summary with more than 3 changes', function () {
        $component = Livewire::test(Index::class);
        $properties = [
            'old' => ['a' => '1', 'b' => '2', 'c' => '3', 'd' => '4', 'e' => '5'],
            'attributes' => ['a' => 'x', 'b' => 'x', 'c' => 'x', 'd' => 'x', 'e' => 'x'],
        ];
        $result = $component->instance()->formatChangesSummary($properties);
        expect($result)->toContain(__('y :count más', ['count' => 2]));
    });

    it('handles Collection in formatChangesSummary', function () {
        $component = Livewire::test(Index::class);
        $properties = collect([
            'old' => ['name' => 'Old'],
            'attributes' => ['name' => 'New'],
        ]);
        $result = $component->instance()->formatChangesSummary($properties);
        expect($result)->toContain('name');
    });

    it('returns sin cambios when properties has no old/attributes', function () {
        $component = Livewire::test(Index::class);
        $properties = ['some_other_key' => 'value'];
        expect($component->instance()->formatChangesSummary($properties))->toBe(__('Sin cambios'));
    });
});
