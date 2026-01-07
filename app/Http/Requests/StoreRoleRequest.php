<?php

namespace App\Http\Requests;

use App\Support\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', \Spatie\Permission\Models\Role::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name'), Rule::in(Roles::all())],
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
