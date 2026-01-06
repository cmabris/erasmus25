<?php

namespace App\Livewire\Admin\Users;

use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    use AuthorizesRequests;

    /**
     * The user being edited.
     */
    public User $user;

    /**
     * User name.
     */
    public string $name = '';

    /**
     * User email.
     */
    public string $email = '';

    /**
     * New password (optional).
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
    public array $selectedRoles = [];

    /**
     * Mount the component.
     */
    public function mount(User $user): void
    {
        $this->authorize('update', $user);

        $this->user = $user;

        // Load user data
        $this->name = $user->name;
        $this->email = $user->email;

        // Load current roles
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
    }

    /**
     * Get all available roles.
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
     * Check if current user can assign roles to this user.
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
     * Update the user.
     */
    public function update(): void
    {
        // Build data array for validation
        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        // Only include password if provided
        if (! empty($this->password)) {
            $data['password'] = $this->password;
            $data['password_confirmation'] = $this->password_confirmation;
        }

        // Get rules and messages from UpdateUserRequest
        $rules = (new UpdateUserRequest)->rules();
        $messages = (new UpdateUserRequest)->messages();

        // Fix validation: UpdateUserRequest tries to get ID from route, but in Livewire we need to pass it manually
        $rules['email'] = ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($this->user->id)];

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

        // Update user basic info
        $this->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Update password only if provided
        if (! empty($validated['password'] ?? null)) {
            $this->user->update([
                'password' => $validated['password'],
            ]);
        }

        // Update roles if user can assign roles and is not editing themselves
        if ($this->canAssignRoles()) {
            // Validate roles - roles are optional (can be empty array)
            if (! empty($this->selectedRoles)) {
                $roleData = ['roles' => $this->selectedRoles];
                $roleRules = [
                    'roles' => ['array'],
                    'roles.*' => ['string', \Illuminate\Validation\Rule::in(Roles::all())],
                ];
                $roleMessages = (new AssignRoleRequest)->messages();

                $roleValidator = Validator::make($roleData, $roleRules, $roleMessages);

                if ($roleValidator->fails()) {
                    foreach ($roleValidator->errors()->messages() as $key => $errorMessages) {
                        foreach ($errorMessages as $message) {
                            $this->addError($key, $message);
                        }
                    }

                    return;
                }
            }

            // Sync roles (empty array removes all roles)
            $this->user->syncRoles($this->selectedRoles);
        }

        $this->dispatch('user-updated', [
            'message' => __('common.messages.updated_successfully'),
        ]);

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.users.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Usuario'),
            ]);
    }
}
