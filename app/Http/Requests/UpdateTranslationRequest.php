<?php

namespace App\Http\Requests;

use App\Models\Translation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $translation = $this->route('translation');

        if (! $translation instanceof Translation) {
            return false;
        }

        return $this->user()?->can('update', $translation) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the translation from route parameter (supports route model binding)
        $translation = $this->route('translation');
        if (! ($translation instanceof Translation)) {
            $translation = Translation::findOrFail($translation);
        }

        $translationId = $translation->id;
        $translatableType = $translation->translatable_type;
        $translatableId = $translation->translatable_id;
        $languageId = $translation->language_id;
        $field = $translation->field;

        return [
            'value' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($translatableType, $translatableId, $languageId, $field, $translationId) {
                    $exists = \App\Models\Translation::where('translatable_type', $translatableType)
                        ->where('translatable_id', $translatableId)
                        ->where('language_id', $languageId)
                        ->where('field', $field)
                        ->where('id', '!=', $translationId)
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
            'value.required' => __('El valor de la traducción es obligatorio.'),
            'value.string' => __('El valor de la traducción debe ser un texto válido.'),
        ];
    }
}
