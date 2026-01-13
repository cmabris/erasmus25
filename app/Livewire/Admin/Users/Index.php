<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    /**
     * Search query for filtering users.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by role.
     */
    #[Url(as: 'rol')]
    public string $filterRole = '';

    /**
     * Filter to show deleted users.
     * Values: '0' (no), '1' (yes)
     */
    #[Url(as: 'eliminados')]
    public string $showDeleted = '0';

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'created_at';

    /**
     * Sort direction (asc or desc).
     */
    #[Url(as: 'direccion')]
    public string $sortDirection = 'desc';

    /**
     * Number of items per page.
     */
    #[Url(as: 'por-pagina')]
    public int $perPage = 15;

    /**
     * Show delete confirmation modal.
     */
    public bool $showDeleteModal = false;

    /**
     * User ID to delete (for confirmation).
     */
    public ?int $userToDelete = null;

    /**
     * Show restore confirmation modal.
     */
    public bool $showRestoreModal = false;

    /**
     * User ID to restore (for confirmation).
     */
    public ?int $userToRestore = null;

    /**
     * Show force delete confirmation modal.
     */
    public bool $showForceDeleteModal = false;

    /**
     * User ID to force delete (for confirmation).
     */
    public ?int $userToForceDelete = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    /**
     * Get paginated and filtered users.
     * Optimized with eager loading and indexed queries.
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->showDeleted === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when($this->showDeleted === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterRole, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->filterRole);
                });
            })
            ->with(['roles', 'permissions']) // Eager load to avoid N+1 queries
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('name', 'asc') // Secondary sort for consistent pagination
            ->paginate($this->perPage);
    }

    /**
     * Get all available roles for filter.
     */
    #[Computed]
    public function roles(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::query()
            ->whereIn('name', Roles::all())
            ->orderBy('name')
            ->get();
    }

    /**
     * Get role display name.
     */
    public function getRoleDisplayName(string $roleName): string
    {
        return match ($roleName) {
            Roles::SUPER_ADMIN => __('Super Administrador'),
            Roles::ADMIN => __('Administrador'),
            Roles::EDITOR => __('Editor'),
            Roles::VIEWER => __('Visualizador'),
            default => $roleName,
        };
    }

    /**
     * Get role badge variant.
     */
    public function getRoleBadgeVariant(string $roleName): string
    {
        return match ($roleName) {
            Roles::SUPER_ADMIN => 'danger',
            Roles::ADMIN => 'warning',
            Roles::EDITOR => 'info',
            Roles::VIEWER => 'neutral',
            default => 'neutral',
        };
    }

    /**
     * Sort by field.
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Confirm delete action.
     */
    public function confirmDelete(int $userId): void
    {
        $this->userToDelete = $userId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a user (SoftDelete).
     */
    public function delete(): void
    {
        if (! $this->userToDelete) {
            return;
        }

        $user = User::findOrFail($this->userToDelete);

        // Check if user is trying to delete themselves
        if ($user->id === auth()->id()) {
            $this->showDeleteModal = false;
            $this->userToDelete = null;
            $this->dispatch('user-delete-error', [
                'message' => __('No puedes eliminarte a ti mismo.'),
            ]);

            return;
        }

        $this->authorize('delete', $user);

        $user->delete();

        $this->userToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('user-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);
    }

    /**
     * Confirm restore action.
     */
    public function confirmRestore(int $userId): void
    {
        $this->userToRestore = $userId;
        $this->showRestoreModal = true;
    }

    /**
     * Restore a deleted user.
     */
    public function restore(): void
    {
        if (! $this->userToRestore) {
            return;
        }

        $user = User::onlyTrashed()->findOrFail($this->userToRestore);

        $this->authorize('restore', $user);

        $user->restore();

        $this->userToRestore = null;
        $this->showRestoreModal = false;
        $this->resetPage();

        $this->dispatch('user-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);
    }

    /**
     * Confirm force delete action.
     */
    public function confirmForceDelete(int $userId): void
    {
        $this->userToForceDelete = $userId;
        $this->showForceDeleteModal = true;
    }

    /**
     * Force delete a user (permanent deletion).
     */
    public function forceDelete(): void
    {
        if (! $this->userToForceDelete) {
            return;
        }

        $user = User::onlyTrashed()->findOrFail($this->userToForceDelete);

        // Check if user is trying to delete themselves
        if ($user->id === auth()->id()) {
            $this->showForceDeleteModal = false;
            $this->userToForceDelete = null;
            $this->dispatch('user-force-delete-error', [
                'message' => __('No puedes eliminarte a ti mismo.'),
            ]);

            return;
        }

        $this->authorize('forceDelete', $user);

        // Note: Activity logs can keep reference to deleted user (causer_id/causer_type)
        // This is optional - we can leave activities as historical record

        $user->forceDelete();

        $this->userToForceDelete = null;
        $this->showForceDeleteModal = false;
        $this->resetPage();

        $this->dispatch('user-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'filterRole', 'showDeleted']);
        $this->showDeleted = '0'; // Reset to '0' (no)
        $this->resetPage();
    }

    /**
     * Handle search input changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Handle role filter changes.
     */
    public function updatedFilterRole(): void
    {
        $this->resetPage();
    }

    /**
     * Handle filter changes.
     */
    public function updatedShowDeleted(): void
    {
        $this->resetPage();
    }

    /**
     * Check if user can create users.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', User::class) ?? false;
    }

    /**
     * Check if user can view deleted users.
     */
    public function canViewDeleted(): bool
    {
        return auth()->user()?->can('viewAny', User::class) ?? false;
    }

    /**
     * Check if a user can be deleted.
     */
    public function canDeleteUser(User $user): bool
    {
        // User cannot delete themselves
        if ($user->id === auth()->id()) {
            return false;
        }

        return auth()->user()?->can('delete', $user) ?? false;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.users.index')
            ->layout('components.layouts.app', [
                'title' => __('Usuarios'),
            ]);
    }
}
