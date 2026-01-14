<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\ErasmusEvent;
use App\Models\NewsletterSubscription;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\Setting;
use App\Models\Translation;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear todos los permisos necesarios
    foreach (Permissions::all() as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    // Crear roles del sistema
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene permisos completos de gestión
    $admin->givePermissionTo([
        Permissions::PROGRAMS_ALL,
        Permissions::CALLS_ALL,
        Permissions::NEWS_ALL,
        Permissions::DOCUMENTS_ALL,
        Permissions::EVENTS_ALL,
        Permissions::USERS_VIEW,
        Permissions::SETTINGS_VIEW,
        Permissions::SETTINGS_EDIT,
        Permissions::TRANSLATIONS_ALL,
        Permissions::NEWSLETTER_VIEW,
    ]);

    // Editor tiene permisos de creación y edición
    $editor->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
        Permissions::CALLS_VIEW,
        Permissions::CALLS_CREATE,
        Permissions::CALLS_EDIT,
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
        Permissions::DOCUMENTS_VIEW,
        Permissions::DOCUMENTS_CREATE,
        Permissions::DOCUMENTS_EDIT,
        Permissions::EVENTS_VIEW,
        Permissions::EVENTS_CREATE,
        Permissions::EVENTS_EDIT,
    ]);

    // Viewer solo tiene permisos de lectura
    $viewer->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
        Permissions::CALLS_VIEW,
        Permissions::NEWS_VIEW,
        Permissions::DOCUMENTS_VIEW,
        Permissions::EVENTS_VIEW,
    ]);

    // Crear datos necesarios para los tests
    $this->academicYear = AcademicYear::factory()->create();
    $this->program = Program::factory()->create(['is_active' => true]);
    $this->call = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
    ]);
    $this->callPhase = CallPhase::factory()->create(['call_id' => $this->call->id]);
    $this->resolution = Resolution::factory()->create(['call_id' => $this->call->id]);
    $this->newsPost = NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'author_id' => User::factory()->create()->id,
    ]);
    $this->newsTag = NewsTag::factory()->create();
    $this->documentCategory = DocumentCategory::factory()->create();
    $this->document = Document::factory()->create([
        'category_id' => $this->documentCategory->id,
        'program_id' => $this->program->id,
        'created_by' => User::factory()->create()->id,
    ]);
    $this->event = ErasmusEvent::factory()->create();
    $this->user = User::factory()->create();
    $this->role = Role::firstOrCreate(['name' => 'test-role', 'guard_name' => 'web']);
    $this->setting = Setting::factory()->create();
    $this->translation = Translation::factory()->create();
    $this->newsletterSubscription = NewsletterSubscription::factory()->create();
});

/*
|--------------------------------------------------------------------------
| Dashboard Route Tests
|--------------------------------------------------------------------------
*/

describe('Admin Dashboard Route', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.dashboard'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access dashboard', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.dashboard'))
            ->assertSuccessful();
    });

    it('allows admin with permissions to access dashboard', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.dashboard'))
            ->assertSuccessful();
    });

    it('allows access to authenticated users without specific permissions', function () {
        // Note: Dashboard allows access to any authenticated user
        // Permissions are checked within the component to show/hide elements
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.dashboard'))
            ->assertSuccessful();
    });
});

/*
|--------------------------------------------------------------------------
| Programs Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Programs Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.programs.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access programs index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.programs.index'))
            ->assertSuccessful();
    });

    it('allows admin with programs permission to access programs index', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PROGRAMS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.programs.index'))
            ->assertSuccessful();
    });

    it('denies access to users without programs permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.programs.index'))
            ->assertForbidden();
    });

    it('allows access to program show route with valid ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PROGRAMS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.programs.show', $this->program->id))
            ->assertSuccessful();
    });

    it('returns 404 for non-existent program ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PROGRAMS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.programs.show', 99999))
            ->assertNotFound();
    });

    it('uses ID for route model binding', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PROGRAMS_VIEW);
        $this->actingAs($user);

        $response = $this->get(route('admin.programs.show', $this->program->id));

        $response->assertSuccessful();
    });
});

/*
|--------------------------------------------------------------------------
| Academic Years Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Academic Years Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.academic-years.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access academic years index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.academic-years.index'))
            ->assertSuccessful();
    });

    it('returns 404 for non-existent academic year ID', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.academic-years.show', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Calls Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Calls Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.calls.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access calls index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.calls.index'))
            ->assertSuccessful();
    });

    it('allows admin with calls permission to access calls index', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.calls.index'))
            ->assertSuccessful();
    });

    it('denies access to users without calls permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.calls.index'))
            ->assertForbidden();
    });

    it('allows access to call show route with valid ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.calls.show', $this->call->id))
            ->assertSuccessful();
    });

    it('returns 404 for non-existent call ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.calls.show', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Calls Phases Routes Tests (Nested)
|--------------------------------------------------------------------------
*/

describe('Admin Calls Phases Routes (Nested)', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.calls.phases.index', $this->call->id))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access phases index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.calls.phases.index', $this->call->id))
            ->assertSuccessful();
    });

    it('allows access to phase show route with valid IDs', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.calls.phases.show', [$this->call->id, $this->callPhase->id]))
            ->assertSuccessful();
    });

    it('returns 404 for non-existent phase ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.calls.phases.show', [$this->call->id, 99999]))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Calls Resolutions Routes Tests (Nested)
|--------------------------------------------------------------------------
*/

describe('Admin Calls Resolutions Routes (Nested)', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.calls.resolutions.index', $this->call->id))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access resolutions index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.calls.resolutions.index', $this->call->id))
            ->assertSuccessful();
    });

    it('allows access to resolution show route with valid IDs', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.calls.resolutions.show', [$this->call->id, $this->resolution->id]))
            ->assertSuccessful();
    });

    it('returns 404 for non-existent resolution ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.calls.resolutions.show', [$this->call->id, 99999]))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| News Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin News Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.news.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access news index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.news.index'))
            ->assertSuccessful();
    });

    it('allows admin with news permission to access news index', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.news.index'))
            ->assertSuccessful();
    });

    it('denies access to users without news permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.news.index'))
            ->assertForbidden();
    });

    it('returns 404 for non-existent news post ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.news.show', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| News Tags Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin News Tags Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.news-tags.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access news tags index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.news-tags.index'))
            ->assertSuccessful();
    });

    it('returns 404 for non-existent news tag ID', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.news-tags.show', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Documents Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Documents Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.documents.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access documents index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.documents.index'))
            ->assertSuccessful();
    });

    it('allows admin with documents permission to access documents index', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.documents.index'))
            ->assertSuccessful();
    });

    it('denies access to users without documents permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.documents.index'))
            ->assertForbidden();
    });

    it('returns 404 for non-existent document ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.documents.show', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Document Categories Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Document Categories Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.document-categories.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access document categories index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.document-categories.index'))
            ->assertSuccessful();
    });

    it('returns 404 for non-existent document category ID', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.document-categories.show', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Events Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Events Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.events.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access events index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.events.index'))
            ->assertSuccessful();
    });

    it('allows admin with events permission to access events index', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EVENTS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.events.index'))
            ->assertSuccessful();
    });

    it('denies access to users without events permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.events.index'))
            ->assertForbidden();
    });

    it('returns 404 for non-existent event ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EVENTS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.events.show', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Users Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Users Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.users.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access users index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.users.index'))
            ->assertSuccessful();
    });

    it('allows admin with users permission to access users index', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.users.index'))
            ->assertSuccessful();
    });

    it('denies access to users without users permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.users.index'))
            ->assertForbidden();
    });

    it('returns 404 for non-existent user ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.users.show', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Roles Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Roles Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.roles.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access roles index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.roles.index'))
            ->assertSuccessful();
    });

    it('denies access to admin users (only super-admin can access roles)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.roles.index'))
            ->assertForbidden();
    });

    it('returns 404 for non-existent role ID', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.roles.show', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Settings Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Settings Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.settings.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access settings index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.settings.index'))
            ->assertSuccessful();
    });

    it('allows admin with settings permission to access settings index', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::SETTINGS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.settings.index'))
            ->assertSuccessful();
    });

    it('returns 404 for non-existent setting ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::SETTINGS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.settings.edit', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Translations Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Translations Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.translations.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access translations index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.translations.index'))
            ->assertSuccessful();
    });

    it('allows admin with translations permission to access translations index', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::TRANSLATIONS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.translations.index'))
            ->assertSuccessful();
    });

    it('returns 404 for non-existent translation ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::TRANSLATIONS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.translations.show', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Audit Logs Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Audit Logs Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.audit-logs.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access audit logs index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.audit-logs.index'))
            ->assertSuccessful();
    });

    it('allows admin to access audit logs index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.audit-logs.index'))
            ->assertSuccessful();
    });

    it('denies access to editor users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $this->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    });

    it('returns 404 for non-existent activity ID', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.audit-logs.show', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Newsletter Routes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Newsletter Routes', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.newsletter.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin to access newsletter index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.newsletter.index'))
            ->assertSuccessful();
    });

    it('allows admin with newsletter permission to access newsletter index', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWSLETTER_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.newsletter.index'))
            ->assertSuccessful();
    });

    it('returns 404 for non-existent newsletter subscription ID', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWSLETTER_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.newsletter.show', 99999))
            ->assertNotFound();
    });
});

/*
|--------------------------------------------------------------------------
| Route Model Binding Tests (ID-based)
|--------------------------------------------------------------------------
*/

describe('Admin Routes - Route Model Binding (ID-based)', function () {
    it('uses ID for program route model binding', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PROGRAMS_VIEW);
        $this->actingAs($user);

        $response = $this->get(route('admin.programs.show', $this->program->id));

        $response->assertSuccessful();
    });

    it('uses ID for call route model binding', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW);
        $this->actingAs($user);

        $response = $this->get(route('admin.calls.show', $this->call->id));

        $response->assertSuccessful();
    });

    it('uses ID for nested call phase route model binding', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW);
        $this->actingAs($user);

        $response = $this->get(route('admin.calls.phases.show', [$this->call->id, $this->callPhase->id]));

        $response->assertSuccessful();
    });

    it('uses ID for nested resolution route model binding', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW);
        $this->actingAs($user);

        $response = $this->get(route('admin.calls.resolutions.show', [$this->call->id, $this->resolution->id]));

        $response->assertSuccessful();
    });

    it('uses ID for news post route model binding', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWS_VIEW);
        $this->actingAs($user);

        $response = $this->get(route('admin.news.show', $this->newsPost->id));

        $response->assertSuccessful();
    });

    it('uses ID for document route model binding', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_VIEW);
        $this->actingAs($user);

        $response = $this->get(route('admin.documents.show', $this->document->id));

        $response->assertSuccessful();
    });

    it('uses ID for event route model binding', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EVENTS_VIEW);
        $this->actingAs($user);

        $response = $this->get(route('admin.events.show', $this->event->id));

        $response->assertSuccessful();
    });

    it('uses ID for user route model binding', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_VIEW);
        $this->actingAs($user);

        $response = $this->get(route('admin.users.show', $this->user->id));

        $response->assertSuccessful();
    });
});

/*
|--------------------------------------------------------------------------
| Route Model Binding - Soft Deletes Tests
|--------------------------------------------------------------------------
*/

describe('Admin Routes - Route Model Binding with Soft Deletes', function () {
    it('returns 404 for soft-deleted program', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PROGRAMS_VIEW);
        $this->actingAs($user);

        // Soft delete a program
        $program = Program::factory()->create();
        $program->delete();

        // Route model binding should exclude soft-deleted models
        $this->get(route('admin.programs.show', $program->id))
            ->assertNotFound();
    });

    it('returns 404 for soft-deleted call', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW);
        $this->actingAs($user);

        // Soft delete a call
        $call = Call::factory()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        $call->delete();

        // Route model binding should exclude soft-deleted models
        $this->get(route('admin.calls.show', $call->id))
            ->assertNotFound();
    });

    it('returns 404 for soft-deleted news post', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWS_VIEW);
        $this->actingAs($user);

        // Soft delete a news post
        $newsPost = NewsPost::factory()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'author_id' => User::factory()->create()->id,
        ]);
        $newsPost->delete();

        // Route model binding should exclude soft-deleted models
        $this->get(route('admin.news.show', $newsPost->id))
            ->assertNotFound();
    });

    it('returns 404 for soft-deleted document', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_VIEW);
        $this->actingAs($user);

        // Soft delete a document
        $document = Document::factory()->create([
            'category_id' => $this->documentCategory->id,
            'program_id' => $this->program->id,
            'created_by' => User::factory()->create()->id,
        ]);
        $document->delete();

        // Route model binding should exclude soft-deleted models
        $this->get(route('admin.documents.show', $document->id))
            ->assertNotFound();
    });

    it('returns 404 for soft-deleted event', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EVENTS_VIEW);
        $this->actingAs($user);

        // Soft delete an event
        $event = ErasmusEvent::factory()->create();
        $event->delete();

        // Route model binding should exclude soft-deleted models
        $this->get(route('admin.events.show', $event->id))
            ->assertNotFound();
    });

    it('handles invalid ID format correctly', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PROGRAMS_VIEW);
        $this->actingAs($user);

        // Try with non-numeric ID
        $this->get(route('admin.programs.show', 'invalid-id'))
            ->assertNotFound();
    });

    it('handles very large ID correctly', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PROGRAMS_VIEW);
        $this->actingAs($user);

        // Try with very large ID (should return 404, not error)
        $this->get(route('admin.programs.show', PHP_INT_MAX))
            ->assertNotFound();
    });
});
