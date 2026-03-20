<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'                  => ['required', 'string', 'max:100'],
            'prenom'               => ['required', 'string', 'max:100'],
            'email'                => ['required', 'email', 'unique:users,email'],
            'motDePasse'           => ['required', 'string', 'min:8', 'confirmed'],
            'telephone'            => ['nullable', 'string'],
            'dateNaissance'        => ['nullable', 'date'],
            'genre'                => ['nullable', 'in:M,F'],
            'localisation'         => ['required', 'string', 'max:100'],
            'categorieId'          => ['required', 'uuid', 'exists:categories,id'],
        ];
    }
}
