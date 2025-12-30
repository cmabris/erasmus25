<?php

namespace App\Http\Requests;

use App\Models\CallPhase;
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

        $callId = $this->input('call_id');

        return [
            'call_id' => ['required', 'exists:calls,id'],
            'call_phase_id' => [
                'required',
                'exists:call_phases,id',
                function ($attribute, $value, $fail) use ($callId) {
                    if ($value && $callId) {
                        $phaseBelongsToCall = CallPhase::where('id', $value)
                            ->where('call_id', $callId)
                            ->exists();

                        if (! $phaseBelongsToCall) {
                            $fail(__('La fase seleccionada no pertenece a la convocatoria especificada.'));
                        }
                    }
                },
            ],
            'type' => ['required', Rule::in(['provisional', 'definitivo', 'alegaciones'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'evaluation_procedure' => ['nullable', 'string'],
            'official_date' => ['required', 'date'],
            'published_at' => ['nullable', 'date'],
            'pdfFile' => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // 10MB
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
            'call_id.required' => __('El ID de la convocatoria es obligatorio.'),
            'call_id.exists' => __('La convocatoria seleccionada no existe o ha sido eliminada.'),
            'call_phase_id.required' => __('Debe seleccionar una fase de la convocatoria.'),
            'call_phase_id.exists' => __('La fase seleccionada no existe o ha sido eliminada.'),
            'type.required' => __('Debe seleccionar un tipo de resolución.'),
            'type.in' => __('El tipo de resolución seleccionado no es válido. Los tipos válidos son: provisional, definitivo o alegaciones.'),
            'title.required' => __('El título de la resolución es obligatorio.'),
            'title.string' => __('El título debe ser un texto válido.'),
            'title.max' => __('El título no puede exceder los :max caracteres.'),
            'description.string' => __('La descripción debe ser un texto válido.'),
            'evaluation_procedure.string' => __('El procedimiento de evaluación debe ser un texto válido.'),
            'official_date.required' => __('La fecha oficial es obligatoria.'),
            'official_date.date' => __('La fecha oficial debe tener un formato de fecha válido (YYYY-MM-DD).'),
            'published_at.date' => __('La fecha de publicación debe tener un formato de fecha válido.'),
            'pdfFile.file' => __('El archivo PDF debe ser un archivo válido.'),
            'pdfFile.mimes' => __('El archivo debe ser un PDF (formato .pdf).'),
            'pdfFile.max' => __('El archivo PDF no puede exceder los :max kilobytes (10MB).'),
        ];
    }
}
