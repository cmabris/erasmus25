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

describe('Admin AuditLogs Show - getModelDisplayName', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('returns correct name for all subject types', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $instance = $component->instance();

        // Test all model mappings
        expect($instance->getModelDisplayName('App\Models\Program'))->toBe(__('Programa'))
            ->and($instance->getModelDisplayName('App\Models\Call'))->toBe(__('Convocatoria'))
            ->and($instance->getModelDisplayName('App\Models\NewsPost'))->toBe(__('Noticia'))
            ->and($instance->getModelDisplayName('App\Models\Document'))->toBe(__('Documento'))
            ->and($instance->getModelDisplayName('App\Models\ErasmusEvent'))->toBe(__('Evento'))
            ->and($instance->getModelDisplayName('App\Models\AcademicYear'))->toBe(__('Año Académico'))
            ->and($instance->getModelDisplayName('App\Models\DocumentCategory'))->toBe(__('Categoría de Documento'))
            ->and($instance->getModelDisplayName('App\Models\NewsTag'))->toBe(__('Etiqueta de Noticia'))
            ->and($instance->getModelDisplayName('App\Models\CallPhase'))->toBe(__('Fase de Convocatoria'))
            ->and($instance->getModelDisplayName('App\Models\Resolution'))->toBe(__('Resolución'));
    });

    it('returns dash for null subject type', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->getModelDisplayName(null))->toBe('-');
    });

    it('returns class basename for unknown subject type', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->getModelDisplayName('App\Models\UnknownModel'))->toBe('UnknownModel');
    });
});

describe('Admin AuditLogs Show - getDescriptionDisplayName', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('returns correct translations for all descriptions', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $instance = $component->instance();

        expect($instance->getDescriptionDisplayName('created'))->toBe(__('Creado'))
            ->and($instance->getDescriptionDisplayName('updated'))->toBe(__('Actualizado'))
            ->and($instance->getDescriptionDisplayName('deleted'))->toBe(__('Eliminado'))
            ->and($instance->getDescriptionDisplayName('publish'))->toBe(__('Publicado'))
            ->and($instance->getDescriptionDisplayName('published'))->toBe(__('Publicado'))
            ->and($instance->getDescriptionDisplayName('archive'))->toBe(__('Archivado'))
            ->and($instance->getDescriptionDisplayName('archived'))->toBe(__('Archivado'))
            ->and($instance->getDescriptionDisplayName('restore'))->toBe(__('Restaurado'))
            ->and($instance->getDescriptionDisplayName('restored'))->toBe(__('Restaurado'));
    });

    it('returns ucfirst for unknown descriptions', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->getDescriptionDisplayName('custom_action'))->toBe('Custom_action');
    });
});

describe('Admin AuditLogs Show - getDescriptionBadgeVariant', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('returns correct badge variants', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $instance = $component->instance();

        // Success variants
        expect($instance->getDescriptionBadgeVariant('created'))->toBe('success')
            ->and($instance->getDescriptionBadgeVariant('publish'))->toBe('success')
            ->and($instance->getDescriptionBadgeVariant('published'))->toBe('success')
            ->and($instance->getDescriptionBadgeVariant('restore'))->toBe('success')
            ->and($instance->getDescriptionBadgeVariant('restored'))->toBe('success');

        // Info variant
        expect($instance->getDescriptionBadgeVariant('updated'))->toBe('info');

        // Danger variants
        expect($instance->getDescriptionBadgeVariant('deleted'))->toBe('danger')
            ->and($instance->getDescriptionBadgeVariant('archive'))->toBe('danger')
            ->and($instance->getDescriptionBadgeVariant('archived'))->toBe('danger');

        // Neutral variant for unknown
        expect($instance->getDescriptionBadgeVariant('unknown_action'))->toBe('neutral');
    });
});

describe('Admin AuditLogs Show - getSubjectUrl', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('returns null for null subject type or id', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $instance = $component->instance();

        expect($instance->getSubjectUrl(null, 1))->toBeNull()
            ->and($instance->getSubjectUrl('App\Models\Program', null))->toBeNull()
            ->and($instance->getSubjectUrl(null, null))->toBeNull();
    });

    it('returns null for unknown subject type', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->getSubjectUrl('App\Models\UnknownModel', 1))->toBeNull();
    });

    it('returns correct URL for mapped models', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $instance = $component->instance();

        expect($instance->getSubjectUrl('App\Models\Program', 1))->toBe(route('admin.programs.show', 1));
    });

    it('returns null for models without routes like CallPhase', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        // CallPhase and Resolution are not in the routeMap
        expect($component->instance()->getSubjectUrl('App\Models\CallPhase', 1))->toBeNull()
            ->and($component->instance()->getSubjectUrl('App\Models\Resolution', 1))->toBeNull();
    });
});

describe('Admin AuditLogs Show - getSubjectTitle', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create(['name' => 'Test Program Name']);
    });

    it('returns dash for null subject', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->getSubjectTitle(null))->toBe('-');
    });

    it('returns title when subject has title property', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $subjectWithTitle = (object) ['title' => 'My Title', 'id' => 1];
        expect($component->instance()->getSubjectTitle($subjectWithTitle))->toBe('My Title');
    });

    it('returns name when subject has name property', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->getSubjectTitle($this->program))->toBe('Test Program Name');
    });

    it('returns fallback with id when no title or name', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $subjectWithoutTitleOrName = (object) ['id' => 42];
        expect($component->instance()->getSubjectTitle($subjectWithoutTitleOrName))->toBe(__('Registro #:id', ['id' => 42]));
    });
});

describe('Admin AuditLogs Show - formatValueForDisplay', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('formats null values', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->formatValueForDisplay(null))->toContain('null');
    });

    it('formats boolean values', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->formatValueForDisplay(true))->toContain('true')
            ->and($component->instance()->formatValueForDisplay(false))->toContain('false');
    });

    it('formats arrays and objects as JSON', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $arrayValue = ['key' => 'value'];
        $result = $component->instance()->formatValueForDisplay($arrayValue);
        expect($result)->toContain('<code')
            ->and($result)->toContain('key');
    });

    it('truncates long strings', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $longString = str_repeat('a', 150);
        $result = $component->instance()->formatValueForDisplay($longString);
        expect($result)->toContain('...')
            ->and(strlen(strip_tags($result)))->toBeLessThan(150);
    });

    it('formats regular strings', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->formatValueForDisplay('simple string'))->toBe('simple string');
    });
});

describe('Admin AuditLogs Show - formatJsonForDisplay', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('formats array data as pretty JSON', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $result = $component->instance()->formatJsonForDisplay(['key' => 'value']);
        expect($result)->toContain('key')
            ->and($result)->toContain('value');
    });

    it('decodes and formats JSON string', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $jsonString = '{"name":"Test","value":123}';
        $result = $component->instance()->formatJsonForDisplay($jsonString);
        expect($result)->toContain('name')
            ->and($result)->toContain('Test');
    });

    it('handles invalid JSON string gracefully', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $invalidJson = 'not valid json';
        $result = $component->instance()->formatJsonForDisplay($invalidJson);
        expect($result)->toContain('not valid json');
    });
});

describe('Admin AuditLogs Show - parseUserAgent', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('returns null for null user agent', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->parseUserAgent(null))->toBeNull();
    });

    it('parses Chrome browser on Windows desktop', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $result = $component->instance()->parseUserAgent($userAgent);

        expect($result['raw'])->toBe($userAgent)
            ->and($result['browser'])->toContain('Chrome')
            ->and($result['os'])->toBe('Windows')
            ->and($result['device'])->toBe('Desktop');
    });

    it('parses Firefox browser on Mac', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15) Gecko/20100101 Firefox/89.0';
        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $result = $component->instance()->parseUserAgent($userAgent);

        expect($result['browser'])->toContain('Firefox')
            ->and($result['os'])->toBe('Mac')
            ->and($result['device'])->toBe('Desktop');
    });

    it('parses mobile user agent', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Safari/605.1.15 Mobile';
        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $result = $component->instance()->parseUserAgent($userAgent);

        expect($result['device'])->toBe('Mobile');
    });

    it('parses Linux user agent', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/91.0 Safari/537.36';
        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $result = $component->instance()->parseUserAgent($userAgent);

        expect($result['os'])->toBe('Linux')
            ->and($result['device'])->toBe('Desktop');
    });

    it('parses Android user agent', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        // Note: The regex in parseUserAgent matches 'Linux' first in Android user agents
        // because the pattern is (Windows|Mac|Linux|Android|iOS) and Linux appears before Android in the string
        $userAgent = 'Mozilla/5.0 (Android 10; SM-G975F) AppleWebKit/537.36 Chrome/80.0 Mobile Safari/537.36';
        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $result = $component->instance()->parseUserAgent($userAgent);

        expect($result['os'])->toBe('Android')
            ->and($result['device'])->toBe('Mobile');
    });
});

describe('Admin AuditLogs Show - hasChanges and getCustomProperties', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('hasChanges returns true when there are changes', function () {
        $activity = Activity::create([
            'description' => 'updated',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'properties' => [
                'old' => ['name' => 'Old'],
                'attributes' => ['name' => 'New'],
            ],
        ]);

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->hasChanges())->toBeTrue();
    });

    it('hasChanges returns false when there are no changes', function () {
        $activity = Activity::create([
            'description' => 'created',
            'subject_type' => Program::class,
            'subject_id' => $this->program->id,
            'properties' => [],
        ]);

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->hasChanges())->toBeFalse();
    });

    it('getCustomProperties returns empty for null properties', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        expect($component->instance()->getCustomProperties(null))->toBe([]);
    });

    it('getCustomProperties excludes system properties', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $properties = [
            'old' => ['name' => 'Old'],
            'attributes' => ['name' => 'New'],
            'ip_address' => '127.0.0.1',
            'ip' => '127.0.0.1',
            'user_agent' => 'Test',
            'userAgent' => 'Test',
            'custom_field' => 'value',
        ];

        $result = $component->instance()->getCustomProperties($properties);
        expect($result)->toBe(['custom_field' => 'value']);
    });

    it('getCustomProperties handles Collection input', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $properties = collect([
            'custom_field' => 'value',
            'ip_address' => '127.0.0.1',
        ]);

        $result = $component->instance()->getCustomProperties($properties);
        expect($result)->toBe(['custom_field' => 'value']);
    });
});

describe('Admin AuditLogs Show - getChangesFromProperties edge cases', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('handles Collection properties', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $properties = collect([
            'old' => ['name' => 'Old'],
            'attributes' => ['name' => 'New'],
        ]);

        $result = $component->instance()->getChangesFromProperties($properties);
        expect($result)->toHaveKey('name')
            ->and($result['name']['old'])->toBe('Old')
            ->and($result['name']['new'])->toBe('New');
    });

    it('excludes unchanged fields', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $properties = [
            'old' => ['name' => 'Same', 'title' => 'Old Title'],
            'attributes' => ['name' => 'Same', 'title' => 'New Title'],
        ];

        $result = $component->instance()->getChangesFromProperties($properties);
        expect($result)->not->toHaveKey('name')
            ->and($result)->toHaveKey('title');
    });
});

describe('Admin AuditLogs Show - getIpAddress and getUserAgent edge cases', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);

        $this->program = Program::factory()->create();
    });

    it('getIpAddress handles Collection input', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $properties = collect(['ip_address' => '192.168.1.1']);
        expect($component->instance()->getIpAddress($properties))->toBe('192.168.1.1');
    });

    it('getIpAddress returns ip when ip_address not present', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $properties = ['ip' => '10.0.0.1'];
        expect($component->instance()->getIpAddress($properties))->toBe('10.0.0.1');
    });

    it('getUserAgent handles Collection input', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $properties = collect(['user_agent' => 'Test Agent']);
        expect($component->instance()->getUserAgent($properties))->toBe('Test Agent');
    });

    it('getUserAgent returns userAgent when user_agent not present', function () {
        $activity = activity()
            ->performedOn($this->program)
            ->causedBy($this->user)
            ->log('created');

        $component = Livewire::test(Show::class, ['activity' => $activity]);
        $properties = ['userAgent' => 'Alternative Agent'];
        expect($component->instance()->getUserAgent($properties))->toBe('Alternative Agent');
    });
});
