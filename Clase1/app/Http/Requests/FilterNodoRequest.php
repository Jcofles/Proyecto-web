<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterNodoRequest extends FormRequest
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
     * Este Request permite filtrar y buscar nodos en tiempo real.
     * Ejemplo de uso: GET /api/nodos/search?q=Salón&tipo_id=1&piso=1&per_page=10
     */
    public function rules(): array
    {
        return [
            'q' => 'sometimes|string|max:255', // búsqueda por nombre
            'tipo_id' => 'sometimes|integer|exists:nodo_tipos,id',
            'piso' => 'sometimes|integer',
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
            'tipo_id.exists' => 'El tipo de nodo no existe',
            'per_page.max' => 'No se pueden traer más de 100 resultados',
        ];
    }
}
