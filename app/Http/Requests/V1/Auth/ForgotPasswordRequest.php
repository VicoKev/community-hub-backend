<?php

namespace App\Http\Requests\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email', 'regex:/^[\w\.\-]+@([\w\-]+\.)+[\w\-]{2,}$/'],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse e-mail ne peut pas dépasser 255 caractères.',
            'email.exists' => 'Cette adresse email n\'est associée à aucun compte.',
            'email.regex' => 'L\'adresse e-mail doit être au format valide.',
        ];
    }
}
