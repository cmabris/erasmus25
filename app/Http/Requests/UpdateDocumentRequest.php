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
        return true;
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
            'created_by' => ['nullable', 'exists:users,id'],
            'updated_by' => ['nullable', 'exists:users,id'],
        ];
    }
}
