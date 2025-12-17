<?php

namespace App\Http\Requests;

use App\Models\Call;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCallRequest extends FormRequest
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
        // Get the call ID from route parameter (supports route model binding)
        $callId = $this->route('call');
        if ($callId instanceof Call) {
            $callId = $callId->id;
        }

        return [
            'program_id' => ['required', 'exists:programs,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('calls', 'slug')->ignore($callId)],
            'type' => ['required', Rule::in(['alumnado', 'personal'])],
            'modality' => ['required', Rule::in(['corta', 'larga'])],
            'number_of_places' => ['required', 'integer', 'min:1'],
            'destinations' => ['required', 'array'],
            'destinations.*' => ['string'],
            'estimated_start_date' => ['nullable', 'date'],
            'estimated_end_date' => ['nullable', 'date', 'after:estimated_start_date'],
            'requirements' => ['nullable', 'string'],
            'documentation' => ['nullable', 'string'],
            'selection_criteria' => ['nullable', 'string'],
            'scoring_table' => ['nullable', 'array'],
            'status' => ['nullable', Rule::in(['borrador', 'abierta', 'cerrada', 'en_baremacion', 'resuelta', 'archivada'])],
            'published_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date'],
        ];
    }
}
