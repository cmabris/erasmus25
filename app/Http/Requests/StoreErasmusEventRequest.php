<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreErasmusEventRequest extends FormRequest
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
            'call_id' => ['nullable', 'exists:calls,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_type' => ['required', Rule::in(['apertura', 'cierre', 'entrevista', 'publicacion_provisional', 'publicacion_definitivo', 'reunion_informativa', 'otro'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_public' => ['nullable', 'boolean'],
            'created_by' => ['nullable', 'exists:users,id'],
        ];
    }
}
