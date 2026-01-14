<?php

use App\Livewire\Admin\Newsletter\Index;
use App\Models\NewsletterSubscription;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::NEWSLETTER_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWSLETTER_DELETE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWSLETTER_EXPORT, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::NEWSLETTER_VIEW,
        Permissions::NEWSLETTER_DELETE,
        Permissions::NEWSLETTER_EXPORT,
    ]);
    $editor->givePermissionTo([
        Permissions::NEWSLETTER_VIEW,
        Permissions::NEWSLETTER_EXPORT,
    ]);
    $viewer->givePermissionTo([
        Permissions::NEWSLETTER_VIEW,
    ]);
});

describe('Admin Newsletter Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.newsletter.index'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with newsletter.view permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWSLETTER_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.newsletter.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.newsletter.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('denies access to users without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.newsletter.index'))
            ->assertForbidden();
    });
});

describe('Admin Newsletter Index - Listing', function () {
    it('displays all subscriptions by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $sub1 = NewsletterSubscription::factory()->create(['email' => 'test1@example.com']);
        $sub2 = NewsletterSubscription::factory()->create(['email' => 'test2@example.com']);

        Livewire::test(Index::class)
            ->assertSee('test1@example.com')
            ->assertSee('test2@example.com');
    });

    it('displays subscription information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $subscription = NewsletterSubscription::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'is_active' => true,
        ]);

        Livewire::test(Index::class)
            ->assertSee('test@example.com')
            ->assertSee('Test User');
    });

    it('displays statistics correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Limpiar suscripciones existentes para este test
        NewsletterSubscription::query()->delete();

        // Crear suscripciones específicas para este test
        // 5 activos: 4 verificados, 1 no verificado
        $active1 = NewsletterSubscription::factory()->create(['is_active' => true, 'verified_at' => null]);
        $active2 = NewsletterSubscription::factory()->create(['is_active' => true, 'verified_at' => now()]);
        $active3 = NewsletterSubscription::factory()->create(['is_active' => true, 'verified_at' => now()]);
        $active4 = NewsletterSubscription::factory()->create(['is_active' => true, 'verified_at' => now()]);
        $active5 = NewsletterSubscription::factory()->create(['is_active' => true, 'verified_at' => now()]);
        // 3 inactivos: ninguno verificado
        $inactive1 = NewsletterSubscription::factory()->create(['is_active' => false, 'verified_at' => null]);
        $inactive2 = NewsletterSubscription::factory()->create(['is_active' => false, 'verified_at' => null]);
        $inactive3 = NewsletterSubscription::factory()->create(['is_active' => false, 'verified_at' => null]);

        $component = Livewire::test(Index::class);
        $statistics = $component->get('statistics');

        expect($statistics['total'])->toBe(8)
            ->and($statistics['active'])->toBe(5)
            ->and($statistics['verified'])->toBe(4);
    });
});

describe('Admin Newsletter Index - Search', function () {
    it('can search subscriptions by email', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $sub1 = NewsletterSubscription::factory()->create(['email' => 'test1@example.com']);
        $sub2 = NewsletterSubscription::factory()->create(['email' => 'test2@example.com']);

        $component = Livewire::test(Index::class)
            ->set('search', 'test1');

        $subscriptions = $component->get('subscriptions');
        $emails = $subscriptions->pluck('email')->toArray();
        expect($emails)->toContain('test1@example.com')
            ->and($emails)->not->toContain('test2@example.com');
    });

    it('can search subscriptions by name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $sub1 = NewsletterSubscription::factory()->create([
            'email' => 'test1@example.com',
            'name' => 'Juan Pérez',
        ]);
        $sub2 = NewsletterSubscription::factory()->create([
            'email' => 'test2@example.com',
            'name' => 'María García',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'Juan')
            ->assertSee('Juan Pérez')
            ->assertDontSee('María García');
    });

    it('resets pagination when searching', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsletterSubscription::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 10)
            ->set('search', 'test');

        expect($component->get('search'))->toBe('test');
        expect($component->get('subscriptions')->currentPage())->toBe(1);
    });
});

describe('Admin Newsletter Index - Filters', function () {
    it('can filter by program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['code' => 'KA121-VET']);
        $program2 = Program::factory()->create(['code' => 'KA131-HED']);

        $sub1 = NewsletterSubscription::factory()->create(['programs' => ['KA121-VET']]);
        $sub2 = NewsletterSubscription::factory()->create(['programs' => ['KA131-HED']]);
        $sub3 = NewsletterSubscription::factory()->create(['programs' => ['KA121-VET', 'KA131-HED']]);

        $component = Livewire::test(Index::class)
            ->set('filterProgram', 'KA121-VET');

        $subscriptions = $component->get('subscriptions');
        $emails = $subscriptions->pluck('email')->toArray();
        expect($emails)->toContain($sub1->email)
            ->and($emails)->toContain($sub3->email)
            ->and($emails)->not->toContain($sub2->email);
    });

    it('can filter by status (active)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $active = NewsletterSubscription::factory()->create(['is_active' => true]);
        $inactive = NewsletterSubscription::factory()->create(['is_active' => false]);

        $component = Livewire::test(Index::class)
            ->set('filterStatus', 'activo');

        $subscriptions = $component->get('subscriptions');
        $emails = $subscriptions->pluck('email')->toArray();
        expect($emails)->toContain($active->email)
            ->and($emails)->not->toContain($inactive->email);
    });

    it('can filter by status (inactive)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $active = NewsletterSubscription::factory()->create(['is_active' => true]);
        $inactive = NewsletterSubscription::factory()->create(['is_active' => false]);

        $component = Livewire::test(Index::class)
            ->set('filterStatus', 'inactivo');

        $subscriptions = $component->get('subscriptions');
        $emails = $subscriptions->pluck('email')->toArray();
        expect($emails)->toContain($inactive->email)
            ->and($emails)->not->toContain($active->email);
    });

    it('can filter by verification (verified)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $verified = NewsletterSubscription::factory()->create(['verified_at' => now()]);
        $unverified = NewsletterSubscription::factory()->create(['verified_at' => null]);

        $component = Livewire::test(Index::class)
            ->set('filterVerification', 'verificado');

        $subscriptions = $component->get('subscriptions');
        $emails = $subscriptions->pluck('email')->toArray();
        expect($emails)->toContain($verified->email)
            ->and($emails)->not->toContain($unverified->email);
    });

    it('can filter by verification (unverified)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $verified = NewsletterSubscription::factory()->create(['verified_at' => now()]);
        $unverified = NewsletterSubscription::factory()->create(['verified_at' => null]);

        $component = Livewire::test(Index::class)
            ->set('filterVerification', 'no-verificado');

        $subscriptions = $component->get('subscriptions');
        $emails = $subscriptions->pluck('email')->toArray();
        expect($emails)->toContain($unverified->email)
            ->and($emails)->not->toContain($verified->email);
    });

    it('resets pagination when changing filters', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsletterSubscription::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 10)
            ->set('filterStatus', 'activo');

        expect($component->get('subscriptions')->currentPage())->toBe(1);
    });
});

describe('Admin Newsletter Index - Sorting', function () {
    it('can sort by email', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsletterSubscription::factory()->create(['email' => 'zeta@example.com']);
        NewsletterSubscription::factory()->create(['email' => 'alpha@example.com']);

        $component = Livewire::test(Index::class)
            ->call('sortBy', 'email');

        $subscriptions = $component->get('subscriptions');
        $emails = $subscriptions->pluck('email')->toArray();
        expect($emails[0])->toBe('alpha@example.com')
            ->and($emails[1])->toBe('zeta@example.com');
    });

    it('can sort by subscribed_at', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Limpiar para evitar interferencias
        NewsletterSubscription::query()->delete();

        $old = NewsletterSubscription::factory()->create([
            'email' => 'old@example.com',
            'subscribed_at' => now()->subDays(10),
        ]);
        $new = NewsletterSubscription::factory()->create([
            'email' => 'new@example.com',
            'subscribed_at' => now(),
        ]);

        $component = Livewire::test(Index::class)
            ->set('sortField', 'subscribed_at')
            ->set('sortDirection', 'desc');

        $subscriptions = $component->get('subscriptions');
        $emails = $subscriptions->pluck('email')->toArray();

        // Verificamos que ambos emails estén presentes
        expect($emails)->toContain($new->email)
            ->and($emails)->toContain($old->email);

        // El más reciente debería estar en una posición anterior (desc)
        $newIndex = array_search($new->email, $emails);
        $oldIndex = array_search($old->email, $emails);
        expect($newIndex)->toBeLessThan($oldIndex);
    });

    it('can toggle sort direction', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsletterSubscription::factory()->create(['email' => 'alpha@example.com']);
        NewsletterSubscription::factory()->create(['email' => 'zeta@example.com']);

        $component = Livewire::test(Index::class);

        // El estado inicial es sortField='subscribed_at' y sortDirection='desc'
        expect($component->get('sortField'))->toBe('subscribed_at')
            ->and($component->get('sortDirection'))->toBe('desc');

        // Llamar a sortBy con 'email' cambia el campo
        $component->call('sortBy', 'email');

        expect($component->get('sortField'))->toBe('email')
            ->and($component->get('sortDirection'))->toBe('asc');

        // Llamar de nuevo al mismo campo cambia la dirección
        $component->call('sortBy', 'email');

        expect($component->get('sortDirection'))->toBe('desc');
    });
});

describe('Admin Newsletter Index - Pagination', function () {
    it('paginates subscriptions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsletterSubscription::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 10);

        expect($component->get('subscriptions')->hasPages())->toBeTrue();
        expect($component->get('subscriptions')->count())->toBe(10);
    });

    it('can change items per page', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsletterSubscription::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 25);

        expect($component->get('perPage'))->toBe(25);
        expect($component->get('subscriptions')->count())->toBe(20);
    });
});

describe('Admin Newsletter Index - Delete', function () {
    it('can delete a subscription', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $subscription = NewsletterSubscription::factory()->create(['email' => 'test@example.com']);

        Livewire::test(Index::class)
            ->call('confirmDelete', $subscription->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('subscriptionToDelete', $subscription->id)
            ->call('delete')
            ->assertDispatched('newsletter-subscription-deleted');

        expect(NewsletterSubscription::find($subscription->id))->toBeNull();
    });

    it('denies delete to unauthorized users', function () {
        $editor = User::factory()->create();
        $editor->assignRole(Roles::EDITOR);
        $this->actingAs($editor);

        $subscription = NewsletterSubscription::factory()->create();

        Livewire::test(Index::class)
            ->call('confirmDelete', $subscription->id)
            ->call('delete')
            ->assertForbidden();

        expect(NewsletterSubscription::find($subscription->id))->not->toBeNull();
    });
});

describe('Admin Newsletter Index - Export', function () {
    it('allows admin to export subscriptions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsletterSubscription::factory()->count(5)->create();

        Livewire::test(Index::class)
            ->call('export')
            ->assertFileDownloaded();
    });

    it('allows editor to export subscriptions', function () {
        $editor = User::factory()->create();
        $editor->assignRole(Roles::EDITOR);
        $this->actingAs($editor);

        NewsletterSubscription::factory()->count(5)->create();

        Livewire::test(Index::class)
            ->call('export')
            ->assertFileDownloaded();
    });

    it('applies filters to export', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $active = NewsletterSubscription::factory()->create(['is_active' => true]);
        $inactive = NewsletterSubscription::factory()->create(['is_active' => false]);

        Livewire::test(Index::class)
            ->set('filterStatus', 'activo')
            ->call('export')
            ->assertFileDownloaded();
    });

    it('denies export to unauthorized users', function () {
        $viewer = User::factory()->create();
        $viewer->assignRole(Roles::VIEWER);
        $this->actingAs($viewer);

        NewsletterSubscription::factory()->count(5)->create();

        Livewire::test(Index::class)
            ->call('export')
            ->assertForbidden();
    });
});

describe('Admin Newsletter Index - Helpers', function () {
    it('can reset filters', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class)
            ->set('search', 'test')
            ->set('filterProgram', 'KA121-VET')
            ->set('filterStatus', 'activo')
            ->set('filterVerification', 'verificado')
            ->call('resetFilters');

        expect($component->get('search'))->toBe('')
            ->and($component->get('filterProgram'))->toBeNull()
            ->and($component->get('filterStatus'))->toBeNull()
            ->and($component->get('filterVerification'))->toBeNull();
    });

    it('can check if user can delete', function () {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);
        $this->actingAs($admin);

        $component = Livewire::test(Index::class);
        expect($component->instance()->canDelete())->toBeTrue();
    });

    it('can check if user can export', function () {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);
        $this->actingAs($admin);

        $component = Livewire::test(Index::class);
        expect($component->instance()->canExport())->toBeTrue();
    });

    it('returns correct status badge variant', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $active = NewsletterSubscription::factory()->create(['is_active' => true]);
        $inactive = NewsletterSubscription::factory()->create(['is_active' => false]);

        $component = Livewire::test(Index::class);
        expect($component->instance()->getStatusBadge($active))->toBe('success')
            ->and($component->instance()->getStatusBadge($inactive))->toBe('danger');
    });

    it('returns correct verification badge variant', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $verified = NewsletterSubscription::factory()->create(['verified_at' => now()]);
        $unverified = NewsletterSubscription::factory()->create(['verified_at' => null]);

        $component = Livewire::test(Index::class);
        expect($component->instance()->getVerificationBadge($verified))->toBe('success')
            ->and($component->instance()->getVerificationBadge($unverified))->toBe('warning');
    });
});
