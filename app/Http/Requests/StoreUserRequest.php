<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\User::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in(\App\Support\Roles::all())],
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
            'password.required' => __('La contraseña es obligatoria.'),
            'password.string' => __('La contraseña debe ser un texto válido.'),
            'password.confirmed' => __('Las contraseñas no coinciden.'),
            'roles.array' => __('Los roles deben ser un array.'),
            'roles.*.string' => __('Cada rol debe ser un texto válido.'),
            'roles.*.in' => __('Uno o más roles seleccionados no son válidos.'),
        ];
    }
}
