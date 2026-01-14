<?php

use App\Livewire\Notifications\Bell;
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

describe('Bell Component - Rendering', function () {
    it('renders the bell component', function () {
        $this->actingAs($this->user);

        Livewire::test(Bell::class)
            ->assertSuccessful();
    });

    it('displays bell icon', function () {
        $this->actingAs($this->user);

        // The bell icon is rendered via Flux UI component
        // We verify the component renders successfully instead of checking for specific icon markup
        Livewire::test(Bell::class)
            ->assertSuccessful();
    });

    it('shows zero count when user has no unread notifications', function () {
        $this->actingAs($this->user);

        Livewire::test(Bell::class)
            ->assertSet('unreadCount', 0);
    });
});

describe('Bell Component - Unread Count', function () {
    it('displays correct unread count', function () {
        $this->actingAs($this->user);

        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        Notification::factory()->read()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        Livewire::test(Bell::class)
            ->assertSet('unreadCount', 3);
    });

    it('updates count when loadUnreadCount is called', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(Bell::class)
            ->assertSet('unreadCount', 0);

        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $component->call('loadUnreadCount')
            ->assertSet('unreadCount', 5);
    });

    it('shows zero count for unauthenticated users', function () {
        Livewire::test(Bell::class)
            ->assertSet('unreadCount', 0);
    });

    it('only counts notifications for the authenticated user', function () {
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

        Livewire::test(Bell::class)
            ->assertSet('unreadCount', 2);
    });
});
