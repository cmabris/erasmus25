<?php

namespace App\Livewire\Admin\Roles;

use App\Http\Requests\UpdateRoleRequest;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    use AuthorizesRequests;

    /**
     * The role being edited.
     */
    public Role $role;

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
    public function mount(Role $role): void
    {
        $this->authorize('update', $role);

        $this->role = $role->load('permissions');

        // Load role data
        $this->name = $role->name;

        // Load current permissions
        $this->permissions = $role->permissions->pluck('name')->toArray();
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
     * Check if this is a system role.
     */
    public function isSystemRole(): bool
    {
        return in_array($this->role->name, Roles::all(), true);
    }

    /**
     * Check if the role name can be changed.
     */
    public function canChangeName(): bool
    {
        return ! $this->isSystemRole();
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
     * Update the role.
     */
    public function update(): void
    {
        // Build data array for validation
        $data = [
            'name' => $this->name,
            'permissions' => $this->permissions,
        ];

        // Get rules and messages from UpdateRoleRequest
        $rules = (new UpdateRoleRequest)->rules();
        $messages = (new UpdateRoleRequest)->messages();

        // Fix validation: UpdateRoleRequest tries to get role from route, but in Livewire we need to pass it manually
        // The rule for name already handles system roles, but we need to ensure the role ID is correct
        $rules['name'] = [
            'required',
            'string',
            'max:255',
            \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($this->role->id),
            \Illuminate\Validation\Rule::in(Roles::all()),
            // If it's a system role, the name must remain the same
            function ($attribute, $value, $fail) {
                if ($this->isSystemRole() && $value !== $this->role->name) {
                    $fail(__('No se puede cambiar el nombre de un rol del sistema.'));
                }
            },
        ];

        // Validate using Validator::make() directly
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            // Add errors manually to component
            foreach ($validator->errors()->messages() as $key => $errorMessages) {
                foreach ($errorMessages as $message) {
                    $this->addError($key, $message);
                }
            }

            return;
        }

        $validated = $validator->validated();

        // Update role name (only if it's not a system role or if it's the same)
        if ($this->name !== $this->role->name && $this->canChangeName()) {
            $this->role->update([
                'name' => $validated['name'],
            ]);
        }

        // Sync permissions
        $this->role->syncPermissions($validated['permissions'] ?? []);

        // Clear permission cache after role update
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->dispatch('role-updated', [
            'message' => __('common.messages.updated_successfully'),
        ]);

        $this->redirect(route('admin.roles.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.roles.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Rol'),
            ]);
    }
}
