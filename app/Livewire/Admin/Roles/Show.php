<?php

namespace App\Livewire\Admin\Roles;

use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Show extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    /**
     * The role to display.
     */
    public Role $role;

    /**
     * Number of users per page.
     */
    public int $usersPerPage = 10;

    /**
     * Show delete confirmation modal.
     */
    public bool $showDeleteModal = false;

    /**
     * Mount the component.
     */
    public function mount(Role $role): void
    {
        $this->authorize('view', $role);

        // Load relationships with eager loading to avoid N+1 queries
        $this->role = $role->load('permissions')->loadCount('users');
    }

    /**
     * Get permissions grouped by module.
     *
     * @return array<string, array<string>>
     */
    #[Computed]
    public function permissionsByModule(): array
    {
        $permissionsByModule = Permissions::byModule();
        $rolePermissions = $this->role->permissions->pluck('name')->toArray();

        $grouped = [];

        foreach ($permissionsByModule as $module => $permissionNames) {
            $modulePermissions = array_intersect($permissionNames, $rolePermissions);

            if (! empty($modulePermissions)) {
                $grouped[$module] = $modulePermissions;
            }
        }

        return $grouped;
    }

    /**
     * Get paginated users with this role.
     * Optimized with eager loading.
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->whereHas('roles', function ($query) {
                $query->where('id', $this->role->id);
            })
            ->with(['roles', 'permissions']) // Eager load to avoid N+1 queries
            ->orderBy('name', 'asc')
            ->orderBy('created_at', 'desc') // Secondary sort for consistent pagination
            ->paginate($this->usersPerPage);
    }

    /**
     * Check if this is a system role.
     */
    public function isSystemRole(): bool
    {
        return in_array($this->role->name, Roles::all(), true);
    }

    /**
     * Check if the role can be deleted.
     */
    public function canDelete(): bool
    {
        if (! auth()->user()?->can('delete', $this->role)) {
            return false;
        }

        // System roles cannot be deleted
        if ($this->isSystemRole()) {
            return false;
        }

        // Roles with users cannot be deleted
        return $this->role->users_count === 0;
    }

    /**
     * Get module display name.
     */
    public function getModuleDisplayName(string $module): string
    {
        return match ($module) {
            'programs' => __('Programas'),
            'calls' => __('Convocatorias'),
            'news' => __('Noticias'),
            'documents' => __('Documentos'),
            'events' => __('Eventos'),
            'users' => __('Usuarios'),
            default => ucfirst($module),
        };
    }

    /**
     * Get permission display name.
     */
    public function getPermissionDisplayName(string $permissionName): string
    {
        $parts = explode('.', $permissionName);

        if (count($parts) === 2) {
            $action = match ($parts[1]) {
                'view' => __('Ver'),
                'create' => __('Crear'),
                'edit' => __('Editar'),
                'delete' => __('Eliminar'),
                'publish' => __('Publicar'),
                '*' => __('Todos'),
                default => ucfirst($parts[1]),
            };

            return $action;
        }

        return $permissionName;
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
     * Confirm delete action.
     */
    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    /**
     * Delete the role.
     */
    public function delete(): void
    {
        // Check if it's a system role
        if ($this->isSystemRole()) {
            $this->showDeleteModal = false;
            $this->dispatch('role-delete-error', [
                'message' => __('No se puede eliminar un rol del sistema.'),
            ]);

            return;
        }

        // Check if role has users assigned
        if ($this->role->users_count > 0) {
            $this->showDeleteModal = false;
            $this->dispatch('role-delete-error', [
                'message' => __('No se puede eliminar el rol porque tiene usuarios asignados.'),
            ]);

            return;
        }

        $this->authorize('delete', $this->role);

        $this->role->delete();

        // Clear permission cache after role deletion
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->dispatch('role-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);

        $this->redirect(route('admin.roles.index'), navigate: true);
    }

    /**
     * Check if user can edit.
     */
    public function canEdit(): bool
    {
        return auth()->user()?->can('update', $this->role) ?? false;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.roles.show')
            ->layout('components.layouts.app', [
                'title' => $this->getRoleDisplayName($this->role->name),
            ]);
    }
}
