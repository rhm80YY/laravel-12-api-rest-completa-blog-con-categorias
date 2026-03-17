<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Cambiar a true para permitir la petición por ahora
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
   public function rules(): array
    {
        // Recuperamos el ID del post que viene en la URL (ej: /api/posts/{post})
        // Nota: Asegurate de que el parámetro de la ruta coincida con el nombre acá.
        $postId = $this->route('post'); 

        return [
            'title'   => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            // Acá está la magia: le decimos que el slug debe ser único, EXCEPTO para este mismo post
            'slug'    => ['sometimes', 'required', 'string', 'max:100', Rule::unique('posts', 'slug')->ignore($postId)],
            'status'  => ['sometimes', 'string', 'in:draft,published'],
            'category_ids'   => ['sometimes', 'array'],
            'category_ids.*' => ['exists:categories,id']
        ];
    }
}
