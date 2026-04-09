<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendPasswordCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo es requerido',
            'email.email' => 'El correo debe ser válido',
        ];
    }
}
