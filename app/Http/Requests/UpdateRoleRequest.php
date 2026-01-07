<?php

namespace App\Http\Requests;

use App\Support\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $role = $this->route('role');

        if (! $role instanceof Role) {
            return false;
        }

        return $this->user()?->can('update', $role) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the role ID from route parameter (supports route model binding)
        $role = $this->route('role');
        $roleId = $role instanceof Role ? $role->id : null;

        // Check if this is a system role (cannot change name)
        $isSystemRole = $role instanceof Role && in_array($role->name, Roles::all(), true);

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId),
                Rule::in(Roles::all()),
                // If it's a system role, the name must remain the same
                function ($attribute, $value, $fail) use ($role, $isSystemRole) {
                    if ($isSystemRole && $role instanceof Role && $value !== $role->name) {
                        $fail(__('No se puede cambiar el nombre de un rol del sistema.'));
                    }
                },
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $validRoles = implode(', ', Roles::all());

        return [
            'name.required' => __('El nombre del rol es obligatorio.'),
            'name.string' => __('El nombre del rol debe ser un texto válido.'),
            'name.max' => __('El nombre del rol no puede tener más de :max caracteres.'),
            'name.unique' => __('Este nombre de rol ya está en uso.'),
            'name.in' => __('El nombre del rol no es válido. Los roles válidos son: :values', ['values' => $validRoles]),
            'permissions.array' => __('Los permisos deben ser un array.'),
            'permissions.*.string' => __('Cada permiso debe ser un texto válido.'),
            'permissions.*.exists' => __('Uno o más permisos seleccionados no existen en el sistema.'),
        ];
    }
}
