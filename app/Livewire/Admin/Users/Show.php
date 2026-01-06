<?php

namespace App\Livewire\Admin\Users;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    /**
     * The user to display.
     */
    public User $user;

    /**
     * Number of audit logs per page.
     */
    public int $auditLogsPerPage = 10;

    /**
     * Modal states.
     */
    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public bool $showForceDeleteModal = false;

    public bool $showAssignRolesModal = false;

    /**
     * Selected roles for assignment.
     *
     * @var array<int, string>
     */
    public array $selectedRoles = [];

    /**
     * Mount the component.
     */
    public function mount(User $user): void
    {
        $this->authorize('view', $user);

        // Load relationships with eager loading to avoid N+1 queries
        $this->user = $user->load(['roles', 'permissions'])->loadCount('auditLogs');

        // Load current roles for modal
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
    }

    /**
     * Get all available roles.
     */
    #[Computed]
    public function roles(): \Illuminate\Database\Eloquent\Collection
    {
        return \Spatie\Permission\Models\Role::query()
            ->whereIn('name', Roles::all())
            ->orderBy('name')
            ->get();
    }

    /**
     * Get paginated audit logs for this user.
     * Optimized with eager loading and indexed queries.
     */
    #[Computed]
    public function auditLogs(): LengthAwarePaginator
    {
        return AuditLog::query()
            ->where('user_id', $this->user->id)
            ->with(['model'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc') // Secondary sort for consistent pagination
            ->paginate($this->auditLogsPerPage);
    }

    /**
     * Get statistics for the user.
     */
    #[Computed]
    public function statistics(): array
    {
        $totalActions = $this->user->audit_logs_count ?? 0;

        // Get actions by type
        $actionsByType = AuditLog::where('user_id', $this->user->id)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        // Get last activity
        $lastActivity = AuditLog::where('user_id', $this->user->id)
            ->latest('created_at')
            ->first();

        return [
            'total_actions' => $totalActions,
            'actions_by_type' => $actionsByType,
            'last_activity' => $lastActivity?->created_at,
        ];
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
     * Get role description.
     */
    public function getRoleDescription(string $roleName): string
    {
        return match ($roleName) {
            Roles::SUPER_ADMIN => __('Acceso total al sistema, incluyendo gestión de usuarios.'),
            Roles::ADMIN => __('Gestión completa de contenido y convocatorias.'),
            Roles::EDITOR => __('Creación y edición de contenido.'),
            Roles::VIEWER => __('Solo lectura, sin permisos de edición.'),
            default => '',
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
     * Get action display name.
     */
    public function getActionDisplayName(string $action): string
    {
        return match ($action) {
            'create' => __('Crear'),
            'update' => __('Actualizar'),
            'delete' => __('Eliminar'),
            'publish' => __('Publicar'),
            'archive' => __('Archivar'),
            'restore' => __('Restaurar'),
            default => ucfirst($action),
        };
    }

    /**
     * Get action badge variant.
     */
    public function getActionBadgeVariant(string $action): string
    {
        return match ($action) {
            'create' => 'success',
            'update' => 'info',
            'delete' => 'danger',
            'publish' => 'success',
            'archive' => 'warning',
            'restore' => 'info',
            default => 'neutral',
        };
    }

    /**
     * Get model display name.
     */
    public function getModelDisplayName(?string $modelType): string
    {
        if (! $modelType) {
            return '-';
        }

        return match ($modelType) {
            'App\Models\Program' => __('Programa'),
            'App\Models\Call' => __('Convocatoria'),
            'App\Models\NewsPost' => __('Noticia'),
            'App\Models\Document' => __('Documento'),
            'App\Models\ErasmusEvent' => __('Evento'),
            'App\Models\AcademicYear' => __('Año Académico'),
            'App\Models\DocumentCategory' => __('Categoría de Documento'),
            'App\Models\NewsTag' => __('Etiqueta de Noticia'),
            default => class_basename($modelType),
        };
    }

    /**
     * Get model URL if available.
     */
    public function getModelUrl(?string $modelType, ?int $modelId): ?string
    {
        if (! $modelType || ! $modelId) {
            return null;
        }

        $routeMap = [
            'App\Models\Program' => 'admin.programs.show',
            'App\Models\Call' => 'admin.calls.show',
            'App\Models\NewsPost' => 'admin.news.show',
            'App\Models\Document' => 'admin.documents.show',
            'App\Models\ErasmusEvent' => 'admin.events.show',
            'App\Models\AcademicYear' => 'admin.academic-years.show',
            'App\Models\DocumentCategory' => 'admin.document-categories.show',
            'App\Models\NewsTag' => 'admin.news-tags.show',
        ];

        $routeName = $routeMap[$modelType] ?? null;

        if (! $routeName) {
            return null;
        }

        try {
            return route($routeName, $modelId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get model title for display.
     */
    public function getModelTitle($model): string
    {
        if (! $model) {
            return '-';
        }

        // Try common title/name fields
        if (isset($model->title)) {
            return $model->title;
        }

        if (isset($model->name)) {
            return $model->name;
        }

        // Fallback to ID
        return __('Registro #:id', ['id' => $model->id ?? '-']);
    }

    /**
     * Format changes JSON for display.
     */
    public function formatChanges(?array $changes): string
    {
        if (! $changes) {
            return '-';
        }

        $formatted = [];

        if (isset($changes['before']) && is_array($changes['before'])) {
            foreach ($changes['before'] as $key => $value) {
                $afterValue = $changes['after'][$key] ?? null;
                if ($value !== $afterValue) {
                    $formatted[] = sprintf(
                        '%s: %s → %s',
                        $key,
                        is_array($value) ? json_encode($value) : ($value ?? 'null'),
                        is_array($afterValue) ? json_encode($afterValue) : ($afterValue ?? 'null')
                    );
                }
            }
        }

        return ! empty($formatted) ? implode(', ', $formatted) : __('Sin cambios');
    }

    /**
     * Delete the user (soft delete).
     */
    public function delete(): void
    {
        // Check if user is trying to delete themselves
        if ($this->user->id === auth()->id()) {
            $this->showDeleteModal = false;
            $this->dispatch('user-delete-error', [
                'message' => __('No puedes eliminarte a ti mismo.'),
            ]);

            return;
        }

        $this->authorize('delete', $this->user);

        $this->user->delete();

        $this->dispatch('user-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    /**
     * Restore the user.
     */
    public function restore(): void
    {
        $this->authorize('restore', $this->user);

        $this->user->restore();

        $this->dispatch('user-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);

        // Reload the user to refresh the view
        $this->user->refresh();
    }

    /**
     * Permanently delete the user.
     */
    public function forceDelete(): void
    {
        // Check if user is trying to delete themselves
        if ($this->user->id === auth()->id()) {
            $this->showForceDeleteModal = false;
            $this->dispatch('user-force-delete-error', [
                'message' => __('No puedes eliminarte a ti mismo.'),
            ]);

            return;
        }

        $this->authorize('forceDelete', $this->user);

        // Set user_id to null in audit logs before deleting
        AuditLog::where('user_id', $this->user->id)->update(['user_id' => null]);

        $this->user->forceDelete();

        $this->dispatch('user-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    /**
     * Check if user can edit.
     */
    public function canEdit(): bool
    {
        return auth()->user()?->can('update', $this->user) ?? false;
    }

    /**
     * Check if user can delete.
     */
    public function canDelete(): bool
    {
        // User cannot delete themselves
        if ($this->user->id === auth()->id()) {
            return false;
        }

        return auth()->user()?->can('delete', $this->user) ?? false;
    }

    /**
     * Check if user can assign roles.
     */
    public function canAssignRoles(): bool
    {
        // User cannot modify their own roles
        if ($this->user->id === auth()->id()) {
            return false;
        }

        return auth()->user()?->can('assignRoles', $this->user) ?? false;
    }

    /**
     * Open assign roles modal.
     */
    public function openAssignRolesModal(): void
    {
        if (! $this->canAssignRoles()) {
            return;
        }

        // Reload current roles
        $this->user->refresh();
        $this->user->load('roles');
        $this->selectedRoles = $this->user->roles->pluck('name')->toArray();

        $this->showAssignRolesModal = true;
    }

    /**
     * Assign roles to user.
     */
    public function assignRoles(): void
    {
        if (! $this->canAssignRoles()) {
            return;
        }

        // Validate roles
        $roleData = ['roles' => $this->selectedRoles];
        $roleRules = [
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', \Illuminate\Validation\Rule::in(Roles::all())],
        ];
        $roleMessages = (new \App\Http\Requests\AssignRoleRequest)->messages();

        $validator = \Illuminate\Support\Facades\Validator::make($roleData, $roleRules, $roleMessages);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $errorMessages) {
                foreach ($errorMessages as $message) {
                    $this->addError($key, $message);
                }
            }

            return;
        }

        // Sync roles
        $this->user->syncRoles($this->selectedRoles);

        // Reload user to refresh roles
        $this->user->refresh();
        $this->user->load('roles');

        $this->showAssignRolesModal = false;

        $this->dispatch('user-roles-updated', [
            'message' => __('Los roles se han actualizado correctamente.'),
        ]);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.users.show')
            ->layout('components.layouts.app', [
                'title' => $this->user->name ?? 'Usuario',
            ]);
    }
}
