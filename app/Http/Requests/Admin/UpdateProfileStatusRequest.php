<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statut'      => ['required', 'in:valide,rejete,suspendu'],
            'motifRejet'  => ['required_if:statut,rejete,suspendu', 'nullable', 'string', 'max:500'],
        ];
    }
}
