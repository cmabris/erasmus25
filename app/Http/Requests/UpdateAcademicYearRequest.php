<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
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
        // Get the academic year ID from route parameter (supports route model binding)
        $academicYearId = $this->route('academic_year');
        if ($academicYearId instanceof AcademicYear) {
            $academicYearId = $academicYearId->id;
        }

        return [
            'year' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/', Rule::unique('academic_years', 'year')->ignore($academicYearId)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
        ];
    }
}
