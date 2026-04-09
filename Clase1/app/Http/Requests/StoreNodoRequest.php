<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNodoRequest extends FormRequest
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
            'nombre' => 'required|string|max:255',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'tipo_id' => 'required|integer|exists:nodo_tipos,id',
            'piso' => 'required|integer',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del nodo es requerido',
            'nombre.max' => 'El nombre no debe exceder 255 caracteres',
            'latitud.required' => 'La latitud es requerida',
            'latitud.numeric' => 'La latitud debe ser un número',
            'latitud.between' => 'La latitud debe estar entre -90 y 90',
            'longitud.required' => 'La longitud es requerida',
            'longitud.numeric' => 'La longitud debe ser un número',
            'longitud.between' => 'La longitud debe estar entre -180 y 180',
            'tipo_id.required' => 'El tipo de nodo es requerido',
            'tipo_id.exists' => 'El tipo de nodo debe ser válido',
            'piso.required' => 'El piso es requerido',
            'piso.integer' => 'El piso debe ser un número entero',
        ];
    }
}
