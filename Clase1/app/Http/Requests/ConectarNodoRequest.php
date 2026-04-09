<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConectarNodoRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'nodo_origen_id' => 'required|integer|exists:nodos,id',
            'nodo_destino_id' => 'required|integer|exists:nodos,id|different:nodo_origen_id',
            'distancia' => 'required|numeric|min:0',
            'bidireccional' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nodo_origen_id.required' => 'El nodo de origen es requerido',
            'nodo_origen_id.exists' => 'El nodo de origen no existe',
            'nodo_destino_id.required' => 'El nodo de destino es requerido',
            'nodo_destino_id.exists' => 'El nodo de destino no existe',
            'nodo_destino_id.different' => 'El nodo de destino debe ser diferente al de origen',
            'distancia.required' => 'La distancia es requerida',
            'distancia.numeric' => 'La distancia debe ser un número',
            'distancia.min' => 'La distancia no puede ser negativa',
        ];
    }
}
