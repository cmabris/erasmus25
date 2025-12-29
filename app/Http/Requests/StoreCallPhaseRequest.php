<?php

namespace App\Http\Requests;

use App\Models\CallPhase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCallPhaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', CallPhase::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'call_id' => ['required', 'exists:calls,id'],
            'phase_type' => ['required', Rule::in(['publicacion', 'solicitudes', 'provisional', 'alegaciones', 'definitivo', 'renuncias', 'lista_espera'])],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer'],
        ];
    }
}
