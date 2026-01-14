<?php

use App\Livewire\Notifications\Index;
use App\Models\AcademicYear;
use App\Models\Notification;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('es');
    $this->user = User::factory()->create();
    $this->program = Program::factory()->create(['is_active' => true]);
    $this->academicYear = AcademicYear::factory()->create();
});

describe('Index Component - Rendering', function () {
    it('renders the index page', function () {
        $this->actingAs($this->user);

        $this->get(route('notifications.index'))
            ->assertOk()
            ->assertSeeLivewire(Index::class);
    });

    it('requires authentication', function () {
        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));
    });

    it('displays notifications page title', function () {
        $this->actingAs($this->user);

        Livewire::test(Index::class)
            ->assertSee(__('notifications.title'));
    });

    it('shows empty state when user has no notifications', function () {
        $this->actingAs($this->user);

        Livewire::test(Index::class)
            ->assertSee(__('notifications.empty.no_notifications'));
    });
});

describe('Index Component - Load Notifications', function () {
    it('displays user notifications', function () {
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
        ]);

        Livewire::test(Index::class)
            ->assertSee('Test Notification');
    });

    it('only shows notifications for the authenticated user', function () {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'My Notification',
        ]);
        Notification::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Other User Notification',
        ]);

        Livewire::test(Index::class)
            ->assertSee('My Notification')
            ->assertDontSee('Other User Notification');
    });

    it('displays notifications in latest order', function () {
        $this->actingAs($this->user);

        $oldNotification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Old Notification',
            'created_at' => now()->subDays(2),
        ]);
        $newNotification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'New Notification',
            'created_at' => now(),
        ]);

        $component = Livewire::test(Index::class);
        $notifications = $component->get('notifications');

        expect($notifications->first()->id)->toBe($newNotification->id);
    });
});

describe('Index Component - Filters', function () {
    it('filters by unread status', function () {
        $this->actingAs($this->user);

        Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        Notification::factory()->read()->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('filter', 'unread');

        expect($component->get('notifications')->count())->toBe(1)
            ->and($component->get('notifications')->first()->is_read)->toBeFalse();
    });

    it('filters by read status', function () {
        $this->actingAs($this->user);

        Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        Notification::factory()->read()->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('filter', 'read');

        expect($component->get('notifications')->count())->toBe(1)
            ->and($component->get('notifications')->first()->is_read)->toBeTrue();
    });

    it('shows all notifications when filter is all', function () {
        $this->actingAs($this->user);

        Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        Notification::factory()->read()->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('filter', 'all');

        expect($component->get('notifications')->count())->toBe(2);
    });

    it('filters by notification type', function () {
        $this->actingAs($this->user);

        Notification::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'convocatoria',
        ]);
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'noticia',
        ]);

        $component = Livewire::test(Index::class)
            ->set('filterType', 'convocatoria');

        expect($component->get('notifications')->count())->toBe(1)
            ->and($component->get('notifications')->first()->type)->toBe('convocatoria');
    });

    it('resets page when filter changes', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(25)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('filter', 'unread')
            ->set('filter', 'read');

        expect($component->get('notifications')->currentPage())->toBe(1);
    });

    it('clears selection when filter changes', function () {
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('selectedNotifications', [$notification->id])
            ->set('filter', 'unread');

        expect($component->get('selectedNotifications'))->toBe([]);
    });

    it('resets filters to default values', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(Index::class)
            ->set('filter', 'unread')
            ->set('filterType', 'convocatoria')
            ->call('resetFilters');

        expect($component->get('filter'))->toBe('all')
            ->and($component->get('filterType'))->toBeNull();
    });
});

describe('Index Component - Pagination', function () {
    it('paginates notifications with 20 per page', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(25)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class);

        expect($component->get('notifications')->count())->toBe(20)
            ->and($component->get('notifications')->hasMorePages())->toBeTrue();
    });

    it('navigates to next page', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(25)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->call('nextPage');

        expect($component->get('notifications')->currentPage())->toBe(2);
    });
});

describe('Index Component - Mark As Read', function () {
    it('marks a notification as read', function () {
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        Livewire::test(Index::class)
            ->call('markAsRead', $notification->id)
            ->assertDispatched('notification-read');

        $notification->refresh();

        expect($notification->is_read)->toBeTrue()
            ->and($notification->read_at)->not->toBeNull();
    });

    it('does not mark notification as read if it belongs to another user', function () {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id,
            'is_read' => false,
        ]);

        Livewire::test(Index::class)
            ->call('markAsRead', $notification->id)
            ->assertNotDispatched('notification-read');

        $notification->refresh();

        expect($notification->is_read)->toBeFalse();
    });

    it('resets page after marking as read', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(25)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->call('nextPage')
            ->call('markAsRead', Notification::first()->id);

        expect($component->get('notifications')->currentPage())->toBe(1);
    });
});

describe('Index Component - Mark All As Read', function () {
    it('marks all notifications as read', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        Livewire::test(Index::class)
            ->call('markAllAsRead')
            ->assertDispatched('notifications-read');

        expect(Notification::where('user_id', $this->user->id)
            ->where('is_read', false)->count())->toBe(0);
    });

    it('clears selection after marking all as read', function () {
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('selectedNotifications', [$notification->id])
            ->call('markAllAsRead');

        expect($component->get('selectedNotifications'))->toBe([]);
    });

    it('resets page after marking all as read', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(25)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->call('nextPage')
            ->call('markAllAsRead');

        expect($component->get('notifications')->currentPage())->toBe(1);
    });
});

describe('Index Component - Delete Notification', function () {
    it('deletes a notification', function () {
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Notification to Delete',
        ]);

        Livewire::test(Index::class)
            ->call('confirmDelete', $notification->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('notificationToDelete', $notification->id)
            ->call('delete')
            ->assertDispatched('notification-deleted')
            ->assertSet('showDeleteModal', false)
            ->assertSet('notificationToDelete', null);

        expect(Notification::find($notification->id))->toBeNull();
    });

    it('does not delete notification if it belongs to another user', function () {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Livewire::test(Index::class)
            ->call('confirmDelete', $notification->id)
            ->call('delete');

        expect(Notification::find($notification->id))->not->toBeNull();
    });

    it('removes notification from selection when deleted', function () {
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('selectedNotifications', [$notification->id])
            ->call('confirmDelete', $notification->id)
            ->call('delete');

        expect($component->get('selectedNotifications'))->not->toContain($notification->id);
    });

    it('resets page after deleting notification', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(25)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->call('nextPage')
            ->call('confirmDelete', Notification::first()->id)
            ->call('delete');

        expect($component->get('notifications')->currentPage())->toBe(1);
    });
});

describe('Index Component - Batch Selection', function () {
    it('toggles select all notifications on current page', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('selectAll', true)
            ->call('toggleSelectAll');

        $notifications = $component->get('notifications');
        $selectedIds = $notifications->pluck('id')->toArray();

        expect($component->get('selectedNotifications'))->toBe($selectedIds);
    });

    it('deselects all when selectAll is false', function () {
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('selectedNotifications', [$notification->id])
            ->set('selectAll', false)
            ->call('toggleSelectAll');

        expect($component->get('selectedNotifications'))->toBe([]);
    });

    it('updates selectAll state when individual notifications are selected', function () {
        $this->actingAs($this->user);

        $notifications = Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('selectedNotifications', [$notifications[0]->id, $notifications[1]->id]);

        expect($component->get('selectAll'))->toBeFalse();

        $component->set('selectedNotifications', $notifications->pluck('id')->toArray());

        expect($component->get('selectAll'))->toBeTrue();
    });

    it('calculates selected count correctly', function () {
        $this->actingAs($this->user);

        $notifications = Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('selectedNotifications', [$notifications[0]->id, $notifications[1]->id]);

        expect($component->get('selectedCount'))->toBe(2);
    });

    it('clears selection', function () {
        $this->actingAs($this->user);

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('selectedNotifications', [$notification->id])
            ->set('selectAll', true)
            ->call('clearSelection');

        expect($component->get('selectedNotifications'))->toBe([])
            ->and($component->get('selectAll'))->toBeFalse();
    });
});

describe('Index Component - Batch Actions', function () {
    it('marks selected notifications as read', function () {
        $this->actingAs($this->user);

        $notifications = Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $component = Livewire::test(Index::class)
            ->set('selectedNotifications', $notifications->pluck('id')->toArray())
            ->call('markSelectedAsRead')
            ->assertDispatched('notifications-read');

        foreach ($notifications as $notification) {
            $notification->refresh();
            expect($notification->is_read)->toBeTrue();
        }
    });

    it('clears selection after marking as read', function () {
        $this->actingAs($this->user);

        $notifications = Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('selectedNotifications', $notifications->pluck('id')->toArray())
            ->call('markSelectedAsRead');

        expect($component->get('selectedNotifications'))->toBe([])
            ->and($component->get('selectAll'))->toBeFalse();
    });

    it('deletes selected notifications', function () {
        $this->actingAs($this->user);

        $notifications = Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('selectedNotifications', $notifications->pluck('id')->toArray())
            ->call('deleteSelected')
            ->assertDispatched('notifications-deleted');

        foreach ($notifications as $notification) {
            expect(Notification::find($notification->id))->toBeNull();
        }
    });

    it('only deletes notifications that belong to the user', function () {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $userNotification = Notification::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $otherNotification = Notification::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('selectedNotifications', [$userNotification->id, $otherNotification->id])
            ->call('deleteSelected');

        expect(Notification::find($userNotification->id))->toBeNull()
            ->and(Notification::find($otherNotification->id))->not->toBeNull();
    });

    it('does nothing if no notifications are selected', function () {
        $this->actingAs($this->user);

        Notification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class)
            ->call('markSelectedAsRead')
            ->assertNotDispatched('notifications-read')
            ->call('deleteSelected')
            ->assertNotDispatched('notifications-deleted');
    });
});

describe('Index Component - Unread Count', function () {
    it('displays correct unread count', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        Notification::factory()->read()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(Index::class);

        expect($component->get('unreadCount'))->toBe(3);
    });

    it('returns zero for unauthenticated users', function () {
        $component = Livewire::actingAs(User::factory()->create())
            ->test(Index::class);

        expect($component->get('unreadCount'))->toBe(0);
    });
});

describe('Index Component - Available Types', function () {
    it('returns available notification types for filtering', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(Index::class);
        $types = $component->get('availableTypes');

        expect($types)->toHaveKeys(['', 'convocatoria', 'resolucion', 'noticia', 'revision', 'sistema']);
    });
});
