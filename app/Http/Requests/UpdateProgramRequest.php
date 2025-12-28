<?php

namespace App\Http\Requests;

use App\Models\Program;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgramRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $program = $this->route('program');

        if (! $program instanceof Program) {
            return false;
        }

        return $this->user()?->can('update', $program) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the program ID from route parameter (supports route model binding)
        $programId = $this->route('program');
        if ($programId instanceof Program) {
            $programId = $programId->id;
        }

        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('programs', 'code')->ignore($programId)],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('programs', 'slug')->ignore($programId)],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp,gif', 'max:5120'], // 5MB max
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
            'code.required' => __('El código del programa es obligatorio.'),
            'code.unique' => __('Este código ya está en uso.'),
            'name.required' => __('El nombre del programa es obligatorio.'),
            'slug.unique' => __('Este slug ya está en uso.'),
            'image.image' => __('El archivo debe ser una imagen.'),
            'image.mimes' => __('La imagen debe ser JPEG, PNG, WebP o GIF.'),
            'image.max' => __('La imagen no puede ser mayor de 5MB.'),
        ];
    }
}
