<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Profile;

use Illuminate\Foundation\Http\FormRequest;

final class UploadFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $collection = $this->input('collection');

        $rules = [
            'collection' => ['required', 'string', 'in:avatar,cv,documents'],
        ];

        return match ($collection) {
            'avatar' => array_merge($rules, [
                'file' => [
                    'required',
                    'file',
                    'mimes:jpeg,png,webp',
                    'max:2048',
                ],
            ]),
            'cv' => array_merge($rules, [
                'file' => [
                    'required',
                    'file',
                    'mimes:pdf',
                    'max:5120',
                ],
            ]),
            'documents' => array_merge($rules, [
                'files' => ['required', 'array', 'min:1', 'max:5'],
                'files.*' => [
                    'file',
                    'mimes:pdf,jpeg,png',
                    'max:5120',
                ],
            ]),
            default => $rules,
        };
    }

    public function messages(): array
    {
        return [
            'collection.in' => 'Collection invalide. Valeurs autorisées : avatar, cv, documents.',
            'file.mimes' => 'Le format du fichier n\'est pas autorisé.',
            'file.max' => 'Le fichier dépasse la taille maximale autorisée.',
            'files.max' => 'Vous ne pouvez uploader que 5 documents à la fois.',
            'files.*.max' => 'Chaque fichier ne peut pas dépasser 5 MB.',
        ];
    }
}
