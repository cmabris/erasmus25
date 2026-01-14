<?php

namespace App\Livewire\Notifications;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Bell extends Component
{
    /**
     * Count of unread notifications.
     */
    public int $unreadCount = 0;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadUnreadCount();
    }

    /**
     * Load the unread notifications count.
     */
    public function loadUnreadCount(): void
    {
        if (! Auth::check()) {
            $this->unreadCount = 0;

            return;
        }

        $this->unreadCount = app(NotificationService::class)->getUnreadCount(Auth::user());
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.notifications.bell');
    }
}
