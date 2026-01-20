<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $setting = $this->route('setting');

        if (! $setting instanceof Setting) {
            return false;
        }

        return $this->user()?->can('update', $setting) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $setting = $this->route('setting');

        if (! $setting instanceof Setting) {
            return [];
        }

        $type = $setting->type;

        // Reglas base para value según tipo
        $valueRules = match ($type) {
            'integer' => ['required', 'integer'],
            'boolean' => ['required', 'boolean'],
            'json' => ['required', 'json'],
            default => ['required', 'string'],
        };

        return [
            'value' => $valueRules,
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $setting = $this->route('setting');

        if (! $setting instanceof Setting) {
            return;
        }

        // Convertir boolean string a boolean real
        if ($setting->type === 'boolean' && $this->has('value')) {
            $value = $this->input('value');

            // Convertir '1', '0', 'true', 'false' a boolean
            if (is_string($value)) {
                $this->merge([
                    'value' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($value === '1' || $value === 'true'),
                ]);
            }
        }

        // Convertir array/objeto a JSON para tipo json
        if ($setting->type === 'json' && $this->has('value')) {
            $value = $this->input('value');

            // Si el valor es array u objeto, convertirlo a JSON string
            if (is_array($value) || is_object($value)) {
                $this->merge([
                    'value' => json_encode($value),
                ]);
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $setting = $this->route('setting');

        if (! $setting instanceof Setting) {
            return [];
        }

        $typeLabel = match ($setting->type) {
            'integer' => __('número entero'),
            'boolean' => __('booleano (sí/no)'),
            'json' => __('JSON válido'),
            default => __('texto'),
        };

        $typeLabel = match ($setting->type) {
            'integer' => __('número entero'),
            'boolean' => __('booleano (sí/no)'),
            'json' => __('JSON válido'),
            default => __('texto'),
        };

        return [
            'value.required' => __('El valor de la configuración es obligatorio.'),
            'value.integer' => __('El valor debe ser un número entero válido.'),
            'value.boolean' => __('El valor debe ser un booleano (sí/no). Use "1" o "true" para activar, "0" o "false" para desactivar.'),
            'value.json' => __('El valor debe ser un JSON válido. Verifique la sintaxis (llaves, comas, comillas).'),
            'value.string' => __('El valor debe ser un texto válido.'),
            'description.string' => __('La descripción debe ser un texto válido.'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'value' => __('valor'),
            'description' => __('descripción'),
        ];
    }
}
