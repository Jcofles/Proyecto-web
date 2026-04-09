<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'nombres' => 'required|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
            'apellidos' => 'required|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
            'email' => 'required|email|max:191',
            'secure_email' => 'required|email|max:191|different:email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nombres.required' => 'El nombre es requerido',
            'nombres.regex' => 'El nombre solo puede contener letras y espacios',
            'apellidos.required' => 'El apellido es requerido',
            'apellidos.regex' => 'El apellido solo puede contener letras y espacios',
            'email.required' => 'El correo es requerido',
            'email.email' => 'El correo debe ser válido',
            'secure_email.required' => 'El correo seguro es requerido',
            'secure_email.email' => 'El correo seguro debe ser válido',
            'secure_email.different' => 'El correo seguro debe ser diferente al correo principal',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password_confirmation.same' => 'Las contraseñas no coinciden',
        ];
    }
}
