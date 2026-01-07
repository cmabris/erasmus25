<?php

namespace App\Livewire\Admin\Roles;

use App\Http\Requests\StoreRoleRequest;
use App\Support\Permissions;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Create extends Component
{
    use AuthorizesRequests;

    /**
     * Role name.
     */
    public string $name = '';

    /**
     * Selected permissions.
     *
     * @var array<int, string>
     */
    public array $permissions = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('create', Role::class);
    }

    /**
     * Get all available permissions grouped by module.
     *
     * @return array<string, array<string, mixed>>
     */
    #[Computed]
    public function availablePermissions(): array
    {
        $permissionsByModule = Permissions::byModule();
        $allPermissions = Permission::query()
            ->whereIn('name', Permissions::all())
            ->get()
            ->keyBy('name');

        $grouped = [];

        foreach ($permissionsByModule as $module => $permissionNames) {
            $modulePermissions = [];

            foreach ($permissionNames as $permissionName) {
                if ($allPermissions->has($permissionName)) {
                    $permission = $allPermissions->get($permissionName);
                    $modulePermissions[] = [
                        'name' => $permission->name,
                        'id' => $permission->id,
                    ];
                }
            }

            if (! empty($modulePermissions)) {
                $grouped[$module] = $modulePermissions;
            }
        }

        return $grouped;
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
     * Select all permissions for a module.
     */
    public function selectAllModulePermissions(string $module): void
    {
        $modulePermissions = $this->availablePermissions[$module] ?? [];

        foreach ($modulePermissions as $permission) {
            if (! in_array($permission['name'], $this->permissions, true)) {
                $this->permissions[] = $permission['name'];
            }
        }
    }

    /**
     * Deselect all permissions for a module.
     */
    public function deselectAllModulePermissions(string $module): void
    {
        $modulePermissions = $this->availablePermissions[$module] ?? [];

        foreach ($modulePermissions as $permission) {
            $key = array_search($permission['name'], $this->permissions, true);
            if ($key !== false) {
                unset($this->permissions[$key]);
            }
        }

        // Re-index array
        $this->permissions = array_values($this->permissions);
    }

    /**
     * Check if all permissions of a module are selected.
     */
    public function areAllModulePermissionsSelected(string $module): bool
    {
        $modulePermissions = $this->availablePermissions[$module] ?? [];

        if (empty($modulePermissions)) {
            return false;
        }

        foreach ($modulePermissions as $permission) {
            if (! in_array($permission['name'], $this->permissions, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Store the role.
     */
    public function store(): void
    {
        // Validate using StoreRoleRequest rules and messages
        $rules = (new StoreRoleRequest)->rules();
        $messages = (new StoreRoleRequest)->messages();
        $validated = $this->validate($rules, $messages);

        // Create role
        $role = Role::create([
            'name' => $validated['name'],
        ]);

        // Assign permissions if provided
        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        // Clear permission cache after role creation
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->dispatch('role-created', [
            'message' => __('common.messages.created_successfully'),
        ]);

        $this->redirect(route('admin.roles.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.roles.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Rol'),
            ]);
    }
}
