<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        if (! $user instanceof User) {
            return false;
        }

        return $this->user()?->can('assignRoles', $user) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'roles' => ['required', 'array'],
            'roles.*' => ['string', Rule::in(Roles::all())],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'roles.required' => __('Debe seleccionar al menos un rol.'),
            'roles.array' => __('Los roles deben ser un array.'),
            'roles.*.string' => __('Cada rol debe ser un texto válido.'),
            'roles.*.in' => __('Uno o más roles seleccionados no son válidos. Los roles válidos son: :values', ['values' => implode(', ', Roles::all())]),
        ];
    }
}
