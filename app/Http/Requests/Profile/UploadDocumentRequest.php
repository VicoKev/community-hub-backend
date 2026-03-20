<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'    => ['required', 'in:cv,photo,doc_legal,autre'],
            'fichier' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $mime = $value->getMimeType();
                    $size = $value->getSize();

                    if ($this->type === 'photo') {
                        if (! in_array($mime, ['image/jpeg', 'image/png'])) {
                            $fail('La photo doit être au format JPG ou PNG.');
                        }
                        if ($size > 1 * 1024 * 1024) {
                            $fail('La photo ne doit pas dépasser 1 Mo.');
                        }
                    } else {
                        if ($mime !== 'application/pdf') {
                            $fail('Le document doit être au format PDF.');
                        }
                        if ($size > 2 * 1024 * 1024) {
                            $fail('Le document ne doit pas dépasser 2 Mo.');
                        }
                    }
                },
            ],
        ];
    }
}
