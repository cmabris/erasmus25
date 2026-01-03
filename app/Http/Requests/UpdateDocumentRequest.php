<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $document = $this->route('document');

        if (! $document instanceof Document) {
            return false;
        }

        return $this->user()?->can('update', $document) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the document ID from route parameter (supports route model binding)
        $documentId = $this->route('document');
        if ($documentId instanceof Document) {
            $documentId = $documentId->id;
        }

        return [
            'category_id' => ['required', 'exists:document_categories,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('documents', 'slug')->ignore($documentId)],
            'description' => ['nullable', 'string'],
            'document_type' => ['required', Rule::in(['convocatoria', 'modelo', 'seguro', 'consentimiento', 'guia', 'faq', 'otro'])],
            'version' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpeg,jpg,png,webp', 'max:20480'], // 20MB
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
            'category_id.required' => __('La categoría del documento es obligatoria.'),
            'category_id.exists' => __('La categoría seleccionada no existe o ha sido eliminada.'),
            'program_id.exists' => __('El programa seleccionado no existe o ha sido eliminado.'),
            'academic_year_id.exists' => __('El año académico seleccionado no existe o ha sido eliminado.'),
            'title.required' => __('El título del documento es obligatorio.'),
            'title.string' => __('El título debe ser un texto válido.'),
            'title.max' => __('El título no puede exceder los :max caracteres.'),
            'slug.string' => __('El slug debe ser un texto válido.'),
            'slug.max' => __('El slug no puede exceder los :max caracteres.'),
            'slug.unique' => __('Este slug ya está en uso. Por favor, elija otro.'),
            'description.string' => __('La descripción debe ser un texto válido.'),
            'document_type.required' => __('Debe seleccionar un tipo de documento.'),
            'document_type.in' => __('El tipo de documento seleccionado no es válido. Los tipos válidos son: convocatoria, modelo, seguro, consentimiento, guia, faq u otro.'),
            'version.string' => __('La versión debe ser un texto válido.'),
            'version.max' => __('La versión no puede exceder los :max caracteres.'),
            'is_active.boolean' => __('El estado activo debe ser verdadero o falso.'),
            'file.file' => __('El archivo debe ser un archivo válido.'),
            'file.mimes' => __('El archivo debe ser de uno de los siguientes tipos: PDF, Word, Excel, PowerPoint, texto, CSV o imagen (JPEG, PNG, WebP).'),
            'file.max' => __('El archivo no puede exceder los :max kilobytes (20MB).'),
        ];
    }
}
