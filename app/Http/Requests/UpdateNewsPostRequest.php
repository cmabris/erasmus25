<?php

namespace App\Http\Requests;

use App\Models\NewsPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $newsPost = $this->route('news_post');

        if (! $newsPost instanceof NewsPost) {
            return false;
        }

        return $this->user()?->can('update', $newsPost) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the news post ID from route parameter (supports route model binding)
        $newsPostId = $this->route('news_post');
        if ($newsPostId instanceof NewsPost) {
            $newsPostId = $newsPostId->id;
        }

        return [
            'program_id' => ['nullable', 'exists:programs,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('news_posts', 'slug')->ignore($newsPostId)],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'host_entity' => ['nullable', 'string', 'max:255'],
            'mobility_type' => ['nullable', Rule::in(['alumnado', 'personal'])],
            'mobility_category' => ['nullable', Rule::in(['FCT', 'job_shadowing', 'intercambio', 'curso', 'otro'])],
            'status' => ['nullable', Rule::in(['borrador', 'en_revision', 'publicado', 'archivado'])],
            'published_at' => ['nullable', 'date'],
            'author_id' => ['nullable', 'exists:users,id'],
            'reviewed_by' => ['nullable', 'exists:users,id'],
            'reviewed_at' => ['nullable', 'date'],
            'featured_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['required', 'exists:news_tags,id'],
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
            'academic_year_id.required' => __('El año académico es obligatorio.'),
            'academic_year_id.exists' => __('El año académico seleccionado no existe.'),
            'program_id.exists' => __('El programa seleccionado no existe.'),
            'title.required' => __('El título es obligatorio.'),
            'title.max' => __('El título no puede tener más de :max caracteres.'),
            'slug.unique' => __('Este slug ya está en uso.'),
            'content.required' => __('El contenido es obligatorio.'),
            'country.max' => __('El país no puede tener más de :max caracteres.'),
            'city.max' => __('La ciudad no puede tener más de :max caracteres.'),
            'host_entity.max' => __('La entidad de acogida no puede tener más de :max caracteres.'),
            'mobility_type.in' => __('El tipo de movilidad debe ser "alumnado" o "personal".'),
            'mobility_category.in' => __('La categoría de movilidad no es válida.'),
            'status.in' => __('El estado no es válido.'),
            'published_at.date' => __('La fecha de publicación debe ser una fecha válida.'),
            'author_id.exists' => __('El autor seleccionado no existe.'),
            'reviewed_by.exists' => __('El revisor seleccionado no existe.'),
            'reviewed_at.date' => __('La fecha de revisión debe ser una fecha válida.'),
            'featured_image.image' => __('La imagen destacada debe ser un archivo de imagen.'),
            'featured_image.mimes' => __('La imagen destacada debe ser de tipo: jpeg, png, jpg, webp o gif.'),
            'featured_image.max' => __('La imagen destacada no puede ser mayor de :max kilobytes.'),
            'tags.array' => __('Las etiquetas deben ser un array.'),
            'tags.*.required' => __('Cada etiqueta es obligatoria.'),
            'tags.*.exists' => __('Una o más etiquetas seleccionadas no existen.'),
        ];
    }
}
