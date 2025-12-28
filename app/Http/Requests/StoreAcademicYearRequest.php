<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicYearRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', AcademicYear::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/', Rule::unique('academic_years', 'year')],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
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
            'year.required' => __('El año académico es obligatorio.'),
            'year.regex' => __('El formato del año académico debe ser YYYY-YYYY (ejemplo: 2024-2025).'),
            'year.unique' => __('Este año académico ya está registrado.'),
            'start_date.required' => __('La fecha de inicio es obligatoria.'),
            'start_date.date' => __('La fecha de inicio debe ser una fecha válida.'),
            'end_date.required' => __('La fecha de fin es obligatoria.'),
            'end_date.date' => __('La fecha de fin debe ser una fecha válida.'),
            'end_date.after' => __('La fecha de fin debe ser posterior a la fecha de inicio.'),
            'is_current.boolean' => __('El campo año actual debe ser verdadero o falso.'),
        ];
    }
}
