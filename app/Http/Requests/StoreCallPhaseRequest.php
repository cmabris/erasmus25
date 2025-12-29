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
        $callId = $this->input('call_id');

        return [
            'call_id' => ['required', 'exists:calls,id'],
            'phase_type' => ['required', Rule::in(['publicacion', 'solicitudes', 'provisional', 'alegaciones', 'definitivo', 'renuncias', 'lista_espera'])],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'is_current' => [
                'nullable',
                'boolean',
                function ($attribute, $value, $fail) use ($callId) {
                    if ($value === true && $callId) {
                        $hasCurrentPhase = CallPhase::where('call_id', $callId)
                            ->where('is_current', true)
                            ->exists();

                        if ($hasCurrentPhase) {
                            $fail(__('Ya existe una fase marcada como actual para esta convocatoria. Solo puede haber una fase actual a la vez.'));
                        }
                    }
                },
            ],
            'order' => [
                'nullable',
                'integer',
                'min:0',
                Rule::unique('call_phases', 'order')
                    ->where('call_id', $callId)
                    ->whereNull('deleted_at'),
            ],
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
            'phase_type.required' => __('Debe seleccionar un tipo de fase.'),
            'phase_type.in' => __('El tipo de fase seleccionado no es válido. Los tipos válidos son: publicación, solicitudes, provisional, alegaciones, definitivo, renuncias o lista de espera.'),
            'name.required' => __('El nombre de la fase es obligatorio.'),
            'name.string' => __('El nombre de la fase debe ser un texto válido.'),
            'name.max' => __('El nombre de la fase no puede exceder los :max caracteres. Por favor, use un nombre más corto.'),
            'description.string' => __('La descripción debe ser un texto válido.'),
            'start_date.date' => __('La fecha de inicio debe tener un formato de fecha válido (YYYY-MM-DD).'),
            'end_date.date' => __('La fecha de fin debe tener un formato de fecha válido (YYYY-MM-DD).'),
            'end_date.after' => __('La fecha de fin debe ser posterior a la fecha de inicio. Por favor, verifique las fechas ingresadas.'),
            'is_current.boolean' => __('El campo "es fase actual" debe ser verdadero o falso.'),
            'order.integer' => __('El orden debe ser un número entero positivo.'),
            'order.min' => __('El orden debe ser mayor o igual a :min. No se permiten valores negativos.'),
            'order.unique' => __('Ya existe otra fase con el mismo orden (:order) para esta convocatoria. Por favor, seleccione un orden diferente o deje que se genere automáticamente.', ['order' => $this->input('order')]),
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'call_id' => __('convocatoria'),
            'phase_type' => __('tipo de fase'),
            'name' => __('nombre'),
            'description' => __('descripción'),
            'start_date' => __('fecha de inicio'),
            'end_date' => __('fecha de fin'),
            'is_current' => __('fase actual'),
            'order' => __('orden'),
        ];
    }
}
