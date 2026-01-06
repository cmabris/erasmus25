<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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

        return $this->user()?->can('update', $user) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the user ID from route parameter (supports route model binding)
        $userId = $this->route('user');
        if ($userId instanceof User) {
            $userId = $userId->id;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
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
            'name.required' => __('El nombre del usuario es obligatorio.'),
            'name.string' => __('El nombre del usuario debe ser un texto válido.'),
            'name.max' => __('El nombre del usuario no puede tener más de :max caracteres.'),
            'email.required' => __('El correo electrónico es obligatorio.'),
            'email.string' => __('El correo electrónico debe ser un texto válido.'),
            'email.email' => __('El correo electrónico debe ser una dirección de correo válida.'),
            'email.max' => __('El correo electrónico no puede tener más de :max caracteres.'),
            'email.unique' => __('Este correo electrónico ya está registrado.'),
            'password.string' => __('La contraseña debe ser un texto válido.'),
            'password.confirmed' => __('Las contraseñas no coinciden.'),
        ];
    }
}
