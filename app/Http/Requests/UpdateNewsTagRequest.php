<?php

namespace App\Http\Requests;

use App\Models\NewsTag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $newsTag = $this->route('news_tag');

        if (! $newsTag instanceof NewsTag) {
            return false;
        }

        return $this->user()?->can('update', $newsTag) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the news tag ID from route parameter (supports route model binding)
        $newsTagId = $this->route('news_tag');
        if ($newsTagId instanceof NewsTag) {
            $newsTagId = $newsTagId->id;
        }

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('news_tags', 'name')->ignore($newsTagId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('news_tags', 'slug')->ignore($newsTagId)],
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
            'name.required' => __('El nombre de la etiqueta es obligatorio.'),
            'name.string' => __('El nombre de la etiqueta debe ser un texto válido.'),
            'name.max' => __('El nombre de la etiqueta no puede tener más de :max caracteres.'),
            'name.unique' => __('Esta etiqueta ya existe.'),
            'slug.string' => __('El slug debe ser un texto válido.'),
            'slug.max' => __('El slug no puede tener más de :max caracteres.'),
            'slug.unique' => __('Este slug ya está en uso.'),
        ];
    }
}
