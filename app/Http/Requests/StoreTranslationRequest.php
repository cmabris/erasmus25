<?php

namespace App\Http\Requests;

use App\Models\Program;
use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTranslationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Translation::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'translatable_type' => [
                'required',
                'string',
                Rule::in([Program::class, Setting::class]),
            ],
            'translatable_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $translatableType = $this->input('translatable_type');
                    if (! $translatableType) {
                        return; // Skip if type not provided yet
                    }

                    $table = match ($translatableType) {
                        Program::class => 'programs',
                        Setting::class => 'settings',
                        default => null,
                    };

                    if ($table && ! \Illuminate\Support\Facades\DB::table($table)->where('id', $value)->exists()) {
                        $fail(__('El registro seleccionado no existe.'));
                    }
                },
            ],
            'language_id' => ['required', 'integer', 'exists:languages,id'],
            'field' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $translatableType = $this->input('translatable_type');
                    if (! $translatableType) {
                        return; // Skip if type not provided yet
                    }

                    $validFields = match ($translatableType) {
                        Program::class => ['name', 'description'],
                        Setting::class => ['value'],
                        default => [],
                    };

                    if (! empty($validFields) && ! in_array($value, $validFields, true)) {
                        $fail(__('El campo seleccionado no es válido para este modelo.'));
                    }
                },
            ],
            'value' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $translatableType = $this->input('translatable_type');
                    $translatableId = $this->input('translatable_id');
                    $languageId = $this->input('language_id');
                    $field = $this->input('field');

                    if (! $translatableType || ! $translatableId || ! $languageId || ! $field) {
                        return; // Skip if required fields not provided yet
                    }

                    $exists = \App\Models\Translation::where('translatable_type', $translatableType)
                        ->where('translatable_id', $translatableId)
                        ->where('language_id', $languageId)
                        ->where('field', $field)
                        ->exists();

                    if ($exists) {
                        $fail(__('Ya existe una traducción para esta combinación de modelo, idioma y campo.'));
                    }
                },
            ],
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
            'translatable_type.required' => __('El tipo de modelo es obligatorio.'),
            'translatable_type.string' => __('El tipo de modelo debe ser un texto válido.'),
            'translatable_type.in' => __('El tipo de modelo seleccionado no es válido.'),
            'translatable_id.required' => __('El registro es obligatorio.'),
            'translatable_id.integer' => __('El registro debe ser un número válido.'),
            'language_id.required' => __('El idioma es obligatorio.'),
            'language_id.integer' => __('El idioma debe ser un número válido.'),
            'language_id.exists' => __('El idioma seleccionado no existe.'),
            'field.required' => __('El campo es obligatorio.'),
            'field.string' => __('El campo debe ser un texto válido.'),
            'field.max' => __('El campo no puede tener más de :max caracteres.'),
            'value.required' => __('El valor de la traducción es obligatorio.'),
            'value.string' => __('El valor de la traducción debe ser un texto válido.'),
        ];
    }
}
