<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
     * Nota: La regla 'sometimes' solo valida el campo si está presente en la request.
     * Esto es perfecto para actualizaciones donde los campos pueden ser opcionales.
     */
    public function rules(): array
    {
        return [
            'nombres' => 'sometimes|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
            'apellidos' => 'sometimes|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
            'email' => [
                'sometimes',
                'email',
                'max:191',
                Rule::unique('users')->ignore($this->user()?->id),
            ],
            // Ejemplo de 'sometimes' con contraseña: solo valida si se proporciona
            'password' => 'sometimes|string|min:8|confirmed',
            'password_confirmation' => 'sometimes|string|min:8',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nombres.regex' => 'El nombre solo puede contener letras y espacios',
            'apellidos.regex' => 'El apellido solo puede contener letras y espacios',
            'email.email' => 'El correo debe ser válido',
            'email.unique' => 'El correo ya está registrado',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ];
    }
}
