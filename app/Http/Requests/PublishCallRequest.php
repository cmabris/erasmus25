<?php

namespace App\Http\Requests;

use App\Models\Call;
use Illuminate\Foundation\Http\FormRequest;

class PublishCallRequest extends FormRequest
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

        return $this->user()?->can('publish', $call) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'published_at' => ['nullable', 'date'],
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
            'published_at.date' => __('La fecha de publicación debe ser una fecha válida.'),
        ];
    }
}
