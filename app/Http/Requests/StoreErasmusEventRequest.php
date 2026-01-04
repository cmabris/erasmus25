<?php

namespace App\Http\Requests;

use App\Models\ErasmusEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreErasmusEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', ErasmusEvent::class) ?? false;
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
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Validate that call_id belongs to program_id if both are present
            if ($this->filled('call_id') && $this->filled('program_id')) {
                $call = \App\Models\Call::find($this->input('call_id'));
                if ($call && $call->program_id != $this->input('program_id')) {
                    $validator->errors()->add(
                        'call_id',
                        __('La convocatoria seleccionada no pertenece al programa seleccionado.')
                    );
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'program_id.exists' => __('El programa seleccionado no existe.'),
            'call_id.exists' => __('La convocatoria seleccionada no existe.'),
            'title.required' => __('El título es obligatorio.'),
            'title.max' => __('El título no puede tener más de :max caracteres.'),
            'description.string' => __('La descripción debe ser texto.'),
            'event_type.required' => __('El tipo de evento es obligatorio.'),
            'event_type.in' => __('El tipo de evento no es válido.'),
            'start_date.required' => __('La fecha de inicio es obligatoria.'),
            'start_date.date' => __('La fecha de inicio debe ser una fecha válida.'),
            'end_date.date' => __('La fecha de fin debe ser una fecha válida.'),
            'end_date.after' => __('La fecha de fin debe ser posterior a la fecha de inicio.'),
            'location.max' => __('La ubicación no puede tener más de :max caracteres.'),
            'is_public.boolean' => __('El campo público debe ser verdadero o falso.'),
            'created_by.exists' => __('El usuario creador seleccionado no existe.'),
            'image.image' => __('La imagen debe ser un archivo de imagen.'),
            'image.mimes' => __('La imagen debe ser de tipo: jpeg, png, jpg, webp o gif.'),
            'image.max' => __('La imagen no puede ser mayor de :max kilobytes.'),
            'images.array' => __('Las imágenes deben ser un array.'),
            'images.*.image' => __('Cada archivo debe ser una imagen.'),
            'images.*.mimes' => __('Cada imagen debe ser de tipo: jpeg, png, jpg, webp o gif.'),
            'images.*.max' => __('Cada imagen no puede ser mayor de :max kilobytes.'),
        ];
    }
}
