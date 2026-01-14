<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Dropdown extends Component
{
    /**
     * Recent unread notifications.
     *
     * @var Collection<int, Notification>
     */
    public Collection $notifications;

    /**
     * Count of unread notifications.
     */
    public int $unreadCount = 0;

    /**
     * Whether the dropdown is open.
     */
    public bool $isOpen = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->notifications = collect();
        $this->loadNotifications();
    }

    /**
     * Load notifications and update count.
     */
    public function loadNotifications(): void
    {
        if (! Auth::check()) {
            $this->notifications = collect();
            $this->unreadCount = 0;

            return;
        }

        $user = Auth::user();
        $this->notifications = $user->notifications()
            ->unread()
            ->recent(7)
            ->latest()
            ->limit(10)
            ->get();

        $this->unreadCount = app(NotificationService::class)->getUnreadCount($user);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $notificationId): void
    {
        $notification = Notification::findOrFail($notificationId);

        // Verify the notification belongs to the current user
        if ($notification->user_id !== Auth::id()) {
            return;
        }

        app(NotificationService::class)->markAsRead($notification);
        $this->loadNotifications();

        // Dispatch event to update other components
        $this->dispatch('notification-read');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): void
    {
        if (! Auth::check()) {
            return;
        }

        app(NotificationService::class)->markAllAsRead(Auth::user());
        $this->loadNotifications();

        // Dispatch event to update other components
        $this->dispatch('notifications-read');
    }

    /**
     * Toggle dropdown open state.
     */
    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;

        if ($this->isOpen) {
            $this->loadNotifications();
        }
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.notifications.dropdown');
    }
}
