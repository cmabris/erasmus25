<?php

use App\Livewire\Notifications\Dropdown;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('es');
    $this->user = User::factory()->create();
});

describe('Dropdown Component - Rendering', function () {
    it('renders the dropdown component', function () {
        $this->actingAs($this->user);

        Livewire::test(Dropdown::class)
            ->assertSuccessful();
    });

    it('initializes with empty notifications collection', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(Dropdown::class);

        expect($component->get('notifications'))->toHaveCount(0)
            ->and($component->get('unreadCount'))->toBe(0)
            ->and($component->get('isOpen'))->toBeFalse();
    });

    it('shows zero count when user has no unread notifications', function () {
        $this->actingAs($this->user);

        Livewire::test(Dropdown::class)
            ->assertSet('unreadCount', 0);
    });
});

describe('Dropdown Component - Load Notifications', function () {
    it('loads unread notifications', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        Notification::factory()->read()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Dropdown::class);

        expect($component->get('notifications'))->toHaveCount(5)
            ->and($component->get('unreadCount'))->toBe(5);
    });

    it('loads only recent notifications (last 7 days)', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
            'created_at' => now()->subDays(5),
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
            'created_at' => now()->subDays(10),
        ]);

        $component = Livewire::test(Dropdown::class);

        expect($component->get('notifications'))->toHaveCount(3);
    });

    it('limits notifications to 10 items', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $component = Livewire::test(Dropdown::class);

        expect($component->get('notifications'))->toHaveCount(10);
    });

    it('only loads notifications for the authenticated user', function () {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $otherUser->id,
            'is_read' => false,
        ]);

        $component = Livewire::test(Dropdown::class);

        expect($component->get('notifications'))->toHaveCount(3);
    });

    it('reloads notifications when loadNotifications is called', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(Dropdown::class)
            ->assertSet('unreadCount', 0);

        Notification::factory()->count(4)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $component->call('loadNotifications');

        expect($component->get('notifications'))->toHaveCount(4)
            ->and($component->get('unreadCount'))->toBe(4);
    });

    it('handles unauthenticated users gracefully', function () {
        $component = Livewire::test(Dropdown::class);

        expect($component->get('notifications'))->toHaveCount(0)
            ->and($component->get('unreadCount'))->toBe(0);
    });
});

describe('Dropdown Component - Mark As Read', function () {
    it('marks a notification as read', function () {
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        Livewire::test(Dropdown::class)
            ->call('markAsRead', $notification->id)
            ->assertDispatched('notification-read');

        $notification->refresh();

        expect($notification->is_read)->toBeTrue()
            ->and($notification->read_at)->not->toBeNull();
    });

    it('reloads notifications after marking as read', function () {
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $component = Livewire::test(Dropdown::class);

        expect($component->get('unreadCount'))->toBe(1);

        $component->call('markAsRead', $notification->id);

        expect($component->get('unreadCount'))->toBe(0);
    });

    it('does not mark notification as read if it belongs to another user', function () {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id,
            'is_read' => false,
        ]);

        Livewire::test(Dropdown::class)
            ->call('markAsRead', $notification->id);

        $notification->refresh();

        expect($notification->is_read)->toBeFalse();
    });

    it('does not dispatch event if notification does not belong to user', function () {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id,
            'is_read' => false,
        ]);

        Livewire::test(Dropdown::class)
            ->call('markAsRead', $notification->id)
            ->assertNotDispatched('notification-read');
    });
});

describe('Dropdown Component - Mark All As Read', function () {
    it('marks all notifications as read', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        Livewire::test(Dropdown::class)
            ->call('markAllAsRead')
            ->assertDispatched('notifications-read');

        expect(Notification::where('user_id', $this->user->id)
            ->where('is_read', false)->count())->toBe(0);
    });

    it('reloads notifications after marking all as read', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $component = Livewire::test(Dropdown::class)
            ->assertSet('unreadCount', 3)
            ->call('markAllAsRead')
            ->assertSet('unreadCount', 0);
    });

    it('only marks notifications for the authenticated user', function () {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        Notification::factory()->count(3)->create([
            'user_id' => $otherUser->id,
            'is_read' => false,
        ]);

        Livewire::test(Dropdown::class)
            ->call('markAllAsRead');

        expect(Notification::where('user_id', $this->user->id)
            ->where('is_read', false)->count())->toBe(0)
            ->and(Notification::where('user_id', $otherUser->id)
                ->where('is_read', false)->count())->toBe(3);
    });

    it('does nothing for unauthenticated users', function () {
        Notification::factory()->count(2)->create([
            'is_read' => false,
        ]);

        Livewire::test(Dropdown::class)
            ->call('markAllAsRead')
            ->assertNotDispatched('notifications-read');
    });
});

describe('Dropdown Component - Toggle', function () {
    it('toggles dropdown open state', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(Dropdown::class)
            ->assertSet('isOpen', false)
            ->call('toggle')
            ->assertSet('isOpen', true)
            ->call('toggle')
            ->assertSet('isOpen', false);
    });

    it('loads notifications when dropdown is opened', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $component = Livewire::test(Dropdown::class)
            ->assertSet('isOpen', false)
            ->call('toggle')
            ->assertSet('isOpen', true);

        expect($component->get('notifications'))->toHaveCount(3);
    });
});
