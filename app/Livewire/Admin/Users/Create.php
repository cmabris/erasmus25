<?php

namespace App\Livewire\Admin\Users;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Create extends Component
{
    use AuthorizesRequests;

    /**
     * User name.
     */
    public string $name = '';

    /**
     * User email.
     */
    public string $email = '';

    /**
     * User password.
     */
    public string $password = '';

    /**
     * Password confirmation.
     */
    public string $password_confirmation = '';

    /**
     * Selected roles.
     *
     * @var array<int, string>
     */
    public array $roles = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('create', User::class);
    }

    /**
     * Get all available roles.
     */
    #[Computed]
    public function availableRoles(): \Illuminate\Database\Eloquent\Collection
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
     * Store the user.
     */
    public function store(): void
    {
        // Validate using StoreUserRequest rules and messages
        $rules = (new StoreUserRequest)->rules();
        $messages = (new StoreUserRequest)->messages();
        $validated = $this->validate($rules, $messages);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        // Assign roles if provided
        if (! empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        $this->dispatch('user-created', [
            'message' => __('common.messages.created_successfully'),
        ]);

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.users.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Usuario'),
            ]);
    }
}
