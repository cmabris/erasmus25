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
        $call = $this->route('call');

        if (! $call instanceof Call) {
            return false;
        }

        return $this->user()?->can('update', $call) ?? false;
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
            'destinations' => ['required', 'array', 'min:1'],
            'destinations.*' => ['required', 'string', 'max:255'],
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

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'program_id.required' => __('El programa es obligatorio.'),
            'program_id.exists' => __('El programa seleccionado no existe.'),
            'academic_year_id.required' => __('El año académico es obligatorio.'),
            'academic_year_id.exists' => __('El año académico seleccionado no existe.'),
            'title.required' => __('El título es obligatorio.'),
            'title.max' => __('El título no puede tener más de :max caracteres.'),
            'slug.unique' => __('Este slug ya está en uso.'),
            'type.required' => __('El tipo de convocatoria es obligatorio.'),
            'type.in' => __('El tipo debe ser "alumnado" o "personal".'),
            'modality.required' => __('La modalidad es obligatoria.'),
            'modality.in' => __('La modalidad debe ser "corta" o "larga".'),
            'number_of_places.required' => __('El número de plazas es obligatorio.'),
            'number_of_places.integer' => __('El número de plazas debe ser un número entero.'),
            'number_of_places.min' => __('El número de plazas debe ser al menos :min.'),
            'destinations.required' => __('Debe especificar al menos un destino.'),
            'destinations.array' => __('Los destinos deben ser un array.'),
            'destinations.min' => __('Debe especificar al menos un destino.'),
            'destinations.*.required' => __('Cada destino es obligatorio.'),
            'destinations.*.string' => __('Cada destino debe ser texto.'),
            'destinations.*.max' => __('Cada destino no puede tener más de :max caracteres.'),
            'estimated_start_date.date' => __('La fecha de inicio estimada debe ser una fecha válida.'),
            'estimated_end_date.date' => __('La fecha de fin estimada debe ser una fecha válida.'),
            'estimated_end_date.after' => __('La fecha de fin estimada debe ser posterior a la fecha de inicio estimada.'),
            'scoring_table.array' => __('El baremo debe ser un array.'),
            'status.in' => __('El estado no es válido.'),
            'published_at.date' => __('La fecha de publicación debe ser una fecha válida.'),
            'closed_at.date' => __('La fecha de cierre debe ser una fecha válida.'),
        ];
    }
}
