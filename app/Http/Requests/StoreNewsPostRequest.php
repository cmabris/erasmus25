<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsPostRequest extends FormRequest
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
        return [
            'program_id' => ['nullable', 'exists:programs,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('news_posts', 'slug')],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'host_entity' => ['nullable', 'string', 'max:255'],
            'mobility_type' => ['nullable', Rule::in(['alumnado', 'personal'])],
            'mobility_category' => ['nullable', Rule::in(['FCT', 'job_shadowing', 'intercambio', 'curso', 'otro'])],
            'status' => ['nullable', Rule::in(['borrador', 'en_revision', 'publicado', 'archivado'])],
            'published_at' => ['nullable', 'date'],
            'author_id' => ['nullable', 'exists:users,id'],
            'reviewed_by' => ['nullable', 'exists:users,id'],
            'reviewed_at' => ['nullable', 'date'],
        ];
    }
}
