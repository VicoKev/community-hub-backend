<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Profile;

use Illuminate\Foundation\Http\FormRequest;

final class ValidateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyPermission(['profile.approve', 'profile.reject']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Vous devez fournir une raison pour le rejet.',
            'reason.min' => 'La raison doit contenir au moins 10 caractères.',
        ];
    }
}
