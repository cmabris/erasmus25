<?php

use App\Livewire\Admin\Newsletter\Show;
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

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::NEWSLETTER_VIEW,
        Permissions::NEWSLETTER_DELETE,
    ]);
    $editor->givePermissionTo([
        Permissions::NEWSLETTER_VIEW,
    ]);
});

describe('Admin Newsletter Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $subscription = NewsletterSubscription::factory()->create();

        $this->get(route('admin.newsletter.show', $subscription))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with newsletter.view permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWSLETTER_VIEW);
        $this->actingAs($user);

        $subscription = NewsletterSubscription::factory()->create();

        $this->get(route('admin.newsletter.show', $subscription))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $subscription = NewsletterSubscription::factory()->create();

        $this->get(route('admin.newsletter.show', $subscription))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('denies access to users without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $subscription = NewsletterSubscription::factory()->create();

        $this->get(route('admin.newsletter.show', $subscription))
            ->assertForbidden();
    });
});

describe('Admin Newsletter Show - Display', function () {
    it('displays subscription information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $subscription = NewsletterSubscription::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'is_active' => true,
            'verified_at' => now(),
        ]);

        Livewire::test(Show::class, ['newsletter_subscription' => $subscription])
            ->assertSee('test@example.com')
            ->assertSee('Test User')
            ->assertSee('Activo')
            ->assertSee('Verificado');
    });

    it('displays programs of interest', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['code' => 'KA121-VET', 'name' => 'Programa 1']);
        $program2 = Program::factory()->create(['code' => 'KA131-HED', 'name' => 'Programa 2']);

        $subscription = NewsletterSubscription::factory()->create([
            'programs' => ['KA121-VET', 'KA131-HED'],
        ]);

        Livewire::test(Show::class, ['newsletter_subscription' => $subscription])
            ->assertSee('Programa 1')
            ->assertSee('Programa 2');
    });

    it('displays program codes when programs not found in database', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $subscription = NewsletterSubscription::factory()->create([
            'programs' => ['INVALID-CODE'],
        ]);

        Livewire::test(Show::class, ['newsletter_subscription' => $subscription])
            ->assertSee('INVALID-CODE');
    });

    it('displays subscription dates correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $subscription = NewsletterSubscription::factory()->create([
            'subscribed_at' => now()->subDays(10),
            'verified_at' => now()->subDays(9),
            'unsubscribed_at' => now()->subDays(1),
        ]);

        Livewire::test(Show::class, ['newsletter_subscription' => $subscription])
            ->assertSee($subscription->subscribed_at->format('d/m/Y'))
            ->assertSee($subscription->verified_at->format('d/m/Y'))
            ->assertSee($subscription->unsubscribed_at->format('d/m/Y'));
    });
});

describe('Admin Newsletter Show - Delete', function () {
    it('can delete a subscription', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $subscription = NewsletterSubscription::factory()->create(['email' => 'test@example.com']);

        Livewire::test(Show::class, ['newsletter_subscription' => $subscription])
            ->call('delete')
            ->assertDispatched('newsletter-subscription-deleted')
            ->assertRedirect(route('admin.newsletter.index'));

        expect(NewsletterSubscription::find($subscription->id))->toBeNull();
    });

    it('denies delete to unauthorized users', function () {
        $editor = User::factory()->create();
        $editor->assignRole(Roles::EDITOR);
        $this->actingAs($editor);

        $subscription = NewsletterSubscription::factory()->create();

        Livewire::test(Show::class, ['newsletter_subscription' => $subscription])
            ->call('delete')
            ->assertForbidden();

        expect(NewsletterSubscription::find($subscription->id))->not->toBeNull();
    });
});

describe('Admin Newsletter Show - Helpers', function () {
    it('can check if user can delete', function () {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);
        $this->actingAs($admin);

        $subscription = NewsletterSubscription::factory()->create();

        $component = Livewire::test(Show::class, ['newsletter_subscription' => $subscription]);
        expect($component->instance()->canDelete())->toBeTrue();
    });

    it('returns correct status badge variant', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $subscription = NewsletterSubscription::factory()->create(['is_active' => true]);

        $component = Livewire::test(Show::class, ['newsletter_subscription' => $subscription]);
        expect($component->instance()->getStatusBadge())->toBe('success');
    });

    it('returns correct verification badge variant', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $subscription = NewsletterSubscription::factory()->create(['verified_at' => now()]);

        $component = Livewire::test(Show::class, ['newsletter_subscription' => $subscription]);
        expect($component->instance()->getVerificationBadge())->toBe('success');
    });

    it('loads program models correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['code' => 'KA121-VET']);
        $program2 = Program::factory()->create(['code' => 'KA131-HED']);

        $subscription = NewsletterSubscription::factory()->create([
            'programs' => ['KA121-VET', 'KA131-HED'],
        ]);

        $component = Livewire::test(Show::class, ['newsletter_subscription' => $subscription]);
        $programModels = $component->get('programModels');

        expect($programModels)->toHaveCount(2)
            ->and($programModels->pluck('code')->toArray())->toContain('KA121-VET', 'KA131-HED');
    });
});
