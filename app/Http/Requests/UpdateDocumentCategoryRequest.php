<?php

namespace App\Http\Requests;

use App\Models\DocumentCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $documentCategory = $this->route('document_category');

        if (! $documentCategory instanceof DocumentCategory) {
            return false;
        }

        return $this->user()?->can('update', $documentCategory) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the document category ID from route parameter (supports route model binding)
        $documentCategoryId = $this->route('document_category');
        if ($documentCategoryId instanceof DocumentCategory) {
            $documentCategoryId = $documentCategoryId->id;
        }

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('document_categories', 'name')->ignore($documentCategoryId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('document_categories', 'slug')->ignore($documentCategoryId)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
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
            'name.required' => __('El nombre de la categoría es obligatorio.'),
            'name.string' => __('El nombre de la categoría debe ser un texto válido.'),
            'name.max' => __('El nombre de la categoría no puede tener más de :max caracteres.'),
            'name.unique' => __('Esta categoría ya existe.'),
            'slug.string' => __('El slug debe ser un texto válido.'),
            'slug.max' => __('El slug no puede tener más de :max caracteres.'),
            'slug.unique' => __('Este slug ya está en uso.'),
            'description.string' => __('La descripción debe ser un texto válido.'),
            'order.integer' => __('El orden debe ser un número entero.'),
        ];
    }
}
