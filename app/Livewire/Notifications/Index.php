<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /**
     * Filter by read status: 'all', 'unread', 'read'.
     */
    #[Url(as: 'filtro')]
    public string $filter = 'all';

    /**
     * Filter by notification type.
     */
    #[Url(as: 'tipo')]
    public ?string $filterType = null;

    /**
     * Show delete confirmation modal.
     */
    public bool $showDeleteModal = false;

    /**
     * Notification ID to delete (for confirmation).
     */
    public ?int $notificationToDelete = null;

    /**
     * Selected notification IDs for batch actions.
     *
     * @var array<int>
     */
    public array $selectedNotifications = [];

    /**
     * Select all notifications on current page.
     */
    public bool $selectAll = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        // Ensure user is authenticated
        if (! Auth::check()) {
            abort(403);
        }
    }

    /**
     * Get paginated and filtered notifications.
     */
    #[Computed]
    public function notifications(): LengthAwarePaginator
    {
        $query = Auth::user()->notifications()->latest();

        // Apply read status filter
        if ($this->filter === 'unread') {
            $query->unread();
        } elseif ($this->filter === 'read') {
            $query->read();
        }

        // Apply type filter
        if ($this->filterType) {
            $query->byType($this->filterType);
        }

        return $query->paginate(20);
    }

    /**
     * Get available notification types for filtering.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function availableTypes(): array
    {
        return [
            '' => __('notifications.filters.all_types'),
            'convocatoria' => __('notifications.types.convocatoria.label'),
            'resolucion' => __('notifications.types.resolucion.label'),
            'noticia' => __('notifications.types.noticia.label'),
            'revision' => __('notifications.types.revision.label'),
            'sistema' => __('notifications.types.sistema.label'),
        ];
    }

    /**
     * Get unread count for the current user.
     */
    #[Computed]
    public function unreadCount(): int
    {
        if (! Auth::check()) {
            return 0;
        }

        return app(NotificationService::class)->getUnreadCount(Auth::user());
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
        $this->resetPage();

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
        $this->resetPage();
        $this->selectedNotifications = [];

        // Dispatch event to update other components
        $this->dispatch('notifications-read');
    }

    /**
     * Toggle select all notifications on current page.
     */
    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            // Select all notifications on current page
            $this->selectedNotifications = $this->notifications->pluck('id')->toArray();
        } else {
            // Deselect all
            $this->selectedNotifications = [];
        }
    }

    /**
     * Update selectAll state when selectedNotifications changes.
     */
    public function updatedSelectedNotifications(): void
    {
        $currentPageIds = $this->notifications->pluck('id')->toArray();
        $this->selectAll = ! empty($currentPageIds) && 
            count(array_intersect($this->selectedNotifications, $currentPageIds)) === count($currentPageIds);
    }

    /**
     * Get count of selected notifications.
     */
    #[Computed]
    public function selectedCount(): int
    {
        return count($this->selectedNotifications);
    }

    /**
     * Mark selected notifications as read.
     */
    public function markSelectedAsRead(): void
    {
        if (empty($this->selectedNotifications) || ! Auth::check()) {
            return;
        }

        $notifications = Notification::whereIn('id', $this->selectedNotifications)
            ->where('user_id', Auth::id())
            ->get();

        foreach ($notifications as $notification) {
            app(NotificationService::class)->markAsRead($notification);
        }

        $this->selectedNotifications = [];
        $this->selectAll = false;
        $this->resetPage();

        // Dispatch event to update other components
        $this->dispatch('notifications-read');
    }

    /**
     * Delete selected notifications.
     */
    public function deleteSelected(): void
    {
        if (empty($this->selectedNotifications) || ! Auth::check()) {
            return;
        }

        $notifications = Notification::whereIn('id', $this->selectedNotifications)
            ->where('user_id', Auth::id())
            ->get();

        foreach ($notifications as $notification) {
            $notification->delete();
        }

        $this->selectedNotifications = [];
        $this->selectAll = false;
        $this->resetPage();

        $this->dispatch('notifications-deleted', [
            'message' => __('notifications.messages.batch_deleted_successfully', ['count' => count($notifications)]),
        ]);
    }

    /**
     * Clear selection.
     */
    public function clearSelection(): void
    {
        $this->selectedNotifications = [];
        $this->selectAll = false;
    }

    /**
     * Confirm delete action.
     */
    public function confirmDelete(int $notificationId): void
    {
        $this->notificationToDelete = $notificationId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a notification.
     */
    public function delete(): void
    {
        if (! $this->notificationToDelete) {
            return;
        }

        $notification = Notification::findOrFail($this->notificationToDelete);

        // Verify the notification belongs to the current user
        if ($notification->user_id !== Auth::id()) {
            $this->showDeleteModal = false;
            $this->notificationToDelete = null;

            return;
        }

        $notification->delete();

        // Remove from selection if it was selected
        $this->selectedNotifications = array_values(array_diff($this->selectedNotifications, [$this->notificationToDelete]));

        $this->notificationToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('notification-deleted', [
            'message' => __('notifications.messages.deleted_successfully'),
        ]);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['filter', 'filterType']);
        $this->resetPage();
    }

    /**
     * Handle filter changes.
     */
    public function updatedFilter(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.notifications.index')
            ->layout('components.layouts.app');
    }
}
