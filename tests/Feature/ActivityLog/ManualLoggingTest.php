<?php

use App\Livewire\Admin\Calls\Resolutions\Show as ResolutionsShow;
use App\Livewire\Admin\Calls\Show as CallsShow;
use App\Livewire\Admin\News\Show as NewsShow;
use App\Livewire\Admin\Users\Show as UsersShow;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear permisos y roles
    Permission::firstOrCreate(['name' => Permissions::CALLS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_DELETE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_PUBLISH, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_DELETE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_PUBLISH, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::USERS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::USERS_EDIT, 'guard_name' => 'web']);

    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $admin->givePermissionTo([
        Permissions::CALLS_VIEW,
        Permissions::CALLS_CREATE,
        Permissions::CALLS_EDIT,
        Permissions::CALLS_DELETE,
        Permissions::CALLS_PUBLISH,
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
        Permissions::NEWS_DELETE,
        Permissions::NEWS_PUBLISH,
        Permissions::USERS_VIEW,
        Permissions::USERS_EDIT,
    ]);

    $this->user = User::factory()->create();
    $this->user->assignRole(Roles::ADMIN);
    $this->actingAs($this->user);
});

describe('Manual Logging - Call Publish', function () {
    it('creates activity log when call is published', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
            'published_at' => null,
        ]);

        // Limpiar logs automáticos previos
        Activity::where('subject_type', Call::class)
            ->where('subject_id', $call->id)
            ->delete();

        Livewire::test(CallsShow::class, ['call' => $call])
            ->call('publish');

        $activity = Activity::where('subject_type', Call::class)
            ->where('subject_id', $call->id)
            ->where('description', 'published')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->description)->toBe('published')
            ->and($activity->causer_id)->toBe($this->user->id)
            ->and($activity->properties)->toHaveKey('ip_address')
            ->and($activity->properties)->toHaveKey('user_agent')
            ->and($activity->properties)->toHaveKey('old_status')
            ->and($activity->properties)->toHaveKey('new_status')
            ->and($activity->properties)->toHaveKey('published_at');
    });

    it('creates activity log when call is restored', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $callId = $call->id;
        $call->delete();

        // Limpiar todos los logs previos
        Activity::where('subject_type', Call::class)
            ->where('subject_id', $callId)
            ->delete();

        $call = Call::withTrashed()->find($callId);

        Livewire::test(CallsShow::class, ['call' => $call])
            ->call('restore');

        // Verificar que se creó al menos un log de restore
        $activities = Activity::where('subject_type', Call::class)
            ->where('subject_id', $callId)
            ->where('description', 'restored')
            ->get();

        expect($activities->count())->toBeGreaterThan(0);

        // Verificar que al menos uno tiene propiedades personalizadas (ip_address)
        $activityWithProps = $activities->first(function ($act) {
            $props = $act->properties;
            if ($props instanceof \Illuminate\Support\Collection) {
                $props = $props->toArray();
            }

            return is_array($props) && isset($props['ip_address']);
        });

        expect($activityWithProps)->not->toBeNull()
            ->and($activityWithProps->description)->toBe('restored')
            ->and($activityWithProps->properties)->toHaveKey('ip_address')
            ->and($activityWithProps->properties)->toHaveKey('user_agent');
    });
});

describe('Manual Logging - News Post Actions', function () {
    it('creates activity log when news post is published', function () {
        $newsPost = NewsPost::factory()->create([
            'status' => 'borrador',
            'published_at' => null,
        ]);

        // Limpiar logs automáticos previos
        Activity::where('subject_type', NewsPost::class)
            ->where('subject_id', $newsPost->id)
            ->delete();

        Livewire::test(NewsShow::class, ['news_post' => $newsPost->fresh()])
            ->call('publish');

        $activity = Activity::where('subject_type', NewsPost::class)
            ->where('subject_id', $newsPost->id)
            ->where('description', 'published')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->description)->toBe('published')
            ->and($activity->properties)->toHaveKey('ip_address')
            ->and($activity->properties)->toHaveKey('user_agent')
            ->and($activity->properties)->toHaveKey('old_status')
            ->and($activity->properties)->toHaveKey('new_status')
            ->and($activity->properties)->toHaveKey('published_at');
    });

    it('creates activity log when news post is unpublished', function () {
        $newsPost = NewsPost::factory()->create([
            'status' => 'publicado',
            'published_at' => now(),
        ]);

        // Limpiar logs automáticos previos
        Activity::where('subject_type', NewsPost::class)
            ->where('subject_id', $newsPost->id)
            ->delete();

        Livewire::test(NewsShow::class, ['news_post' => $newsPost->fresh()])
            ->call('unpublish');

        $activity = Activity::where('subject_type', NewsPost::class)
            ->where('subject_id', $newsPost->id)
            ->where('description', 'unpublished')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->description)->toBe('unpublished')
            ->and($activity->properties)->toHaveKey('ip_address')
            ->and($activity->properties)->toHaveKey('user_agent')
            ->and($activity->properties)->toHaveKey('old_status')
            ->and($activity->properties)->toHaveKey('new_status');
    });

    it('creates activity log when news post is restored', function () {
        $newsPost = NewsPost::factory()->create([
            'status' => 'borrador', // Asegurar que tiene status
        ]);
        $newsPostId = $newsPost->id;
        $newsPost->delete();
        $newsPost = NewsPost::withTrashed()->find($newsPostId);

        // Limpiar logs previos
        Activity::where('subject_type', NewsPost::class)
            ->where('subject_id', $newsPostId)
            ->delete();

        Livewire::test(NewsShow::class, ['news_post' => $newsPost])
            ->call('restore');

        // Verificar que se creó al menos un log de restore
        $activities = Activity::where('subject_type', NewsPost::class)
            ->where('subject_id', $newsPostId)
            ->where('description', 'restored')
            ->get();

        expect($activities->count())->toBeGreaterThan(0);

        // Verificar que al menos uno tiene propiedades personalizadas (ip_address)
        $activityWithProps = $activities->first(function ($act) {
            $props = $act->properties;
            if ($props instanceof \Illuminate\Support\Collection) {
                $props = $props->toArray();
            }

            return is_array($props) && isset($props['ip_address']);
        });

        expect($activityWithProps)->not->toBeNull()
            ->and($activityWithProps->description)->toBe('restored')
            ->and($activityWithProps->properties)->toHaveKey('ip_address')
            ->and($activityWithProps->properties)->toHaveKey('user_agent');
    });
});

describe('Manual Logging - Resolution Actions', function () {
    it('creates activity log when resolution is published', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'published_at' => null,
        ]);

        // Limpiar logs previos
        Activity::where('subject_type', Resolution::class)
            ->where('subject_id', $resolution->id)
            ->delete();

        Livewire::test(ResolutionsShow::class, ['call' => $call, 'resolution' => $resolution])
            ->call('publish');

        $activity = Activity::where('subject_type', Resolution::class)
            ->where('subject_id', $resolution->id)
            ->where('description', 'published')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->description)->toBe('published')
            ->and($activity->properties)->toHaveKey('ip_address')
            ->and($activity->properties)->toHaveKey('user_agent')
            ->and($activity->properties)->toHaveKey('was_published')
            ->and($activity->properties)->toHaveKey('published_at');
    });

    it('creates activity log when resolution is unpublished', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'published_at' => now(),
        ]);

        // Limpiar logs previos
        Activity::where('subject_type', Resolution::class)
            ->where('subject_id', $resolution->id)
            ->delete();

        Livewire::test(ResolutionsShow::class, ['call' => $call, 'resolution' => $resolution])
            ->call('unpublish');

        $activity = Activity::where('subject_type', Resolution::class)
            ->where('subject_id', $resolution->id)
            ->where('description', 'unpublished')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->description)->toBe('unpublished')
            ->and($activity->properties)->toHaveKey('ip_address')
            ->and($activity->properties)->toHaveKey('user_agent');
    });

    it('creates activity log when resolution is restored', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
        ]);

        $resolutionId = $resolution->id;
        $resolution->delete();
        $resolution = Resolution::withTrashed()->find($resolutionId);

        // Limpiar logs previos
        Activity::where('subject_type', Resolution::class)
            ->where('subject_id', $resolutionId)
            ->delete();

        Livewire::test(ResolutionsShow::class, ['call' => $call, 'resolution' => $resolution])
            ->call('restore');

        // Verificar que se creó al menos un log de restore
        $activities = Activity::where('subject_type', Resolution::class)
            ->where('subject_id', $resolutionId)
            ->where('description', 'restored')
            ->get();

        expect($activities->count())->toBeGreaterThan(0);

        // Verificar que al menos uno tiene propiedades personalizadas (ip_address)
        $activityWithProps = $activities->first(function ($act) {
            $props = $act->properties;
            if ($props instanceof \Illuminate\Support\Collection) {
                $props = $props->toArray();
            }

            return is_array($props) && isset($props['ip_address']);
        });

        expect($activityWithProps)->not->toBeNull()
            ->and($activityWithProps->description)->toBe('restored')
            ->and($activityWithProps->properties)->toHaveKey('ip_address')
            ->and($activityWithProps->properties)->toHaveKey('user_agent');
    });
});

describe('Manual Logging - User Role Assignment', function () {
    beforeEach(function () {
        // Crear roles necesarios
        Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);
    });

    it('creates activity log when roles are assigned to user', function () {
        $targetUser = User::factory()->create();

        // Limpiar logs previos
        Activity::where('subject_type', User::class)
            ->where('subject_id', $targetUser->id)
            ->delete();

        Livewire::test(UsersShow::class, ['user' => $targetUser])
            ->set('selectedRoles', [Roles::EDITOR])
            ->call('assignRoles');

        $activity = Activity::where('subject_type', User::class)
            ->where('subject_id', $targetUser->id)
            ->where('description', 'roles_assigned')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->description)->toBe('roles_assigned')
            ->and($activity->causer_id)->toBe($this->user->id)
            ->and($activity->properties)->toHaveKey('ip_address')
            ->and($activity->properties)->toHaveKey('user_agent')
            ->and($activity->properties)->toHaveKey('old_roles')
            ->and($activity->properties)->toHaveKey('new_roles');
    });

    it('logs old and new roles correctly', function () {
        $targetUser = User::factory()->create();
        $targetUser->assignRole(Roles::VIEWER);

        // Limpiar logs previos
        Activity::where('subject_type', User::class)
            ->where('subject_id', $targetUser->id)
            ->delete();

        Livewire::test(UsersShow::class, ['user' => $targetUser])
            ->set('selectedRoles', [Roles::EDITOR])
            ->call('assignRoles');

        $activity = Activity::where('subject_type', User::class)
            ->where('subject_id', $targetUser->id)
            ->where('description', 'roles_assigned')
            ->first();

        $properties = $activity->properties;

        expect($properties['old_roles'])->toContain(Roles::VIEWER)
            ->and($properties['new_roles'])->toContain(Roles::EDITOR);
    });
});

describe('Manual Logging - Custom Properties', function () {
    it('saves IP address in manual logging', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
        ]);

        // Limpiar logs previos
        Activity::where('subject_type', Call::class)
            ->where('subject_id', $call->id)
            ->delete();

        Livewire::test(CallsShow::class, ['call' => $call])
            ->call('publish');

        $activity = Activity::where('subject_type', Call::class)
            ->where('subject_id', $call->id)
            ->where('description', 'published')
            ->first();

        expect($activity->properties['ip_address'])->not->toBeNull();
    });

    it('saves user agent in manual logging', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
        ]);

        // Limpiar logs previos
        Activity::where('subject_type', Call::class)
            ->where('subject_id', $call->id)
            ->delete();

        Livewire::test(CallsShow::class, ['call' => $call])
            ->call('publish');

        $activity = Activity::where('subject_type', Call::class)
            ->where('subject_id', $call->id)
            ->where('description', 'published')
            ->first();

        expect($activity->properties['user_agent'])->not->toBeNull();
    });

    it('saves context-specific properties in manual logging', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
            'published_at' => null,
        ]);

        // Limpiar logs previos
        Activity::where('subject_type', Call::class)
            ->where('subject_id', $call->id)
            ->delete();

        Livewire::test(CallsShow::class, ['call' => $call])
            ->call('publish');

        $activity = Activity::where('subject_type', Call::class)
            ->where('subject_id', $call->id)
            ->where('description', 'published')
            ->first();

        $properties = $activity->properties;

        expect($properties['old_status'])->toBe('borrador')
            ->and($properties['new_status'])->toBe('abierta')
            ->and($properties)->toHaveKey('published_at');
    });
});
