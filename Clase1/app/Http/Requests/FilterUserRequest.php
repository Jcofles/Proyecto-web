<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterUserRequest extends FormRequest
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
     * Este Request permite filtrar y buscar usuarios en tiempo real.
     * Ejemplo de uso: GET /api/users/search?q=Juan&status=activo&per_page=10
     */
    public function rules(): array
    {
        return [
            'q' => 'sometimes|string|max:255', // búsqueda por nombre o email
            'status' => 'sometimes|string|in:activo,inactivo,bloqueado,eliminado',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'q.max' => 'La búsqueda no debe exceder 255 caracteres',
            'status.in' => 'El estado debe ser: activo, inactivo, bloqueado o eliminado',
            'per_page.max' => 'No se pueden traer más de 100 resultados',
        ];
    }
}
