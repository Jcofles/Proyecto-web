<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendPasswordCodeRequest extends FormRequest
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
            'email' => 'required|email|exists:users,email',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El correo es requerido',
            'email.email' => 'El correo debe ser válido',
            'email.exists' => 'El correo no está registrado',
        ];
    }
}
