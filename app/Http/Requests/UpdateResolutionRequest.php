<?php

namespace App\Http\Requests;

use App\Models\Resolution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResolutionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $resolution = $this->route('resolution');

        if (! $resolution instanceof Resolution) {
            return false;
        }

        return $this->user()?->can('update', $resolution) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the resolution ID from route parameter (supports route model binding)
        $resolutionId = $this->route('resolution');
        if ($resolutionId instanceof Resolution) {
            $resolutionId = $resolutionId->id;
        }

        return [
            'call_id' => ['required', 'exists:calls,id'],
            'call_phase_id' => ['required', 'exists:call_phases,id'],
            'type' => ['required', Rule::in(['provisional', 'definitivo', 'alegaciones'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'evaluation_procedure' => ['nullable', 'string'],
            'official_date' => ['required', 'date'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
