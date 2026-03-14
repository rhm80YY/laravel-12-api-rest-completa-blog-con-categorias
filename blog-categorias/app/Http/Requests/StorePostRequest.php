<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule; 
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Determina si el usuario está autorizado a hacer este request.
     */
    public function authorize(): bool
    {
        // Por ahora lo dejamos en true para que cualquiera pueda crear un post.
        // En los Días 6-7 (Bloque 2), conectaremos esto con las Policies de Laravel.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */

    /**
     * Obtiene las reglas de validación que se aplicarán al request.
     */
    public function rules(): array
    {
        return [
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'slug'    => ['required', 'string', 'max:100', 'unique:posts,slug'],
            'status'  => ['sometimes', 'string', 'in:draft,published']
        ];
    }
}
