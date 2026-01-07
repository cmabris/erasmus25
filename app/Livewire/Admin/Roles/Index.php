<?php

namespace App\Livewire\Admin\Roles;

use App\Support\Roles;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    /**
     * Search query for filtering roles.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'name';

    /**
     * Sort direction (asc or desc).
     */
    #[Url(as: 'direccion')]
    public string $sortDirection = 'asc';

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
     * Role ID to delete (for confirmation).
     */
    public ?int $roleToDelete = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Role::class);
    }

    /**
     * Get paginated and filtered roles.
     * Optimized with eager loading and counts.
     */
    #[Computed]
    public function roles(): LengthAwarePaginator
    {
        return Role::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->withCount(['users', 'permissions']) // Count without loading all records
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('name', 'asc') // Secondary sort for consistent pagination
            ->paginate($this->perPage);
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
    public function confirmDelete(int $roleId): void
    {
        $this->roleToDelete = $roleId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a role.
     */
    public function delete(): void
    {
        if (! $this->roleToDelete) {
            return;
        }

        $role = Role::withCount(['users'])->findOrFail($this->roleToDelete);

        // Check if it's a system role
        if (in_array($role->name, Roles::all(), true)) {
            $this->showDeleteModal = false;
            $this->roleToDelete = null;
            $this->dispatch('role-delete-error', [
                'message' => __('No se puede eliminar un rol del sistema.'),
            ]);

            return;
        }

        // Check if role has users assigned
        if ($role->users_count > 0) {
            $this->showDeleteModal = false;
            $this->roleToDelete = null;
            $this->dispatch('role-delete-error', [
                'message' => __('No se puede eliminar el rol porque tiene usuarios asignados.'),
            ]);

            return;
        }

        $this->authorize('delete', $role);

        $role->delete();

        // Clear permission cache after role deletion
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->roleToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('role-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search']);
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
     * Check if user can create roles.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', Role::class) ?? false;
    }

    /**
     * Check if a role can be deleted.
     * Uses the loaded count to avoid additional queries.
     */
    public function canDeleteRole(Role $role): bool
    {
        if (! auth()->user()?->can('delete', $role)) {
            return false;
        }

        // System roles cannot be deleted
        if (in_array($role->name, Roles::all(), true)) {
            return false;
        }

        // Roles with users cannot be deleted
        return ($role->users_count ?? 0) === 0;
    }

    /**
     * Check if a role is a system role.
     */
    public function isSystemRole(Role $role): bool
    {
        return in_array($role->name, Roles::all(), true);
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
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.roles.index')
            ->layout('components.layouts.app', [
                'title' => __('Roles y Permisos'),
            ]);
    }
}
