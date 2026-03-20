<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bio'               => ['nullable', 'string', 'max:1000'],
            'localisation'      => ['nullable', 'string', 'max:100'],
            'quartier'          => ['nullable', 'string', 'max:100'],
            'arrondissement'    => ['nullable', 'string', 'max:100'],
            'siteWeb'           => ['nullable', 'url', 'max:255'],
            'reseauxSociaux'    => ['nullable', 'array'],
            'reseauxSociaux.*'  => ['string', 'url'],
            'niveauEtude'       => ['nullable', 'in:Bac,Licence,Master,Doctorat,Autre'],
            'secteurActivite'   => ['nullable', 'string', 'max:150'],
            'metier'            => ['nullable', 'string', 'max:150'],
            'competences'       => ['nullable', 'array'],
            'competences.*'     => ['string', 'max:100'],
            'visibiliteContact' => ['nullable', 'in:PUBLIC,PRIVE'],
            'categorieId'       => ['nullable', 'uuid', 'exists:categories,id'],
        ];
    }
}
