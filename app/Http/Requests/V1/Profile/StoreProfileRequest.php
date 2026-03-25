<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Profile;

use App\Enums\EducationLevel;
use App\Enums\ProfileCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreProfileRequest extends FormRequest
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
        return [
            'phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'bio' => ['nullable', 'string', 'max:2000'],

            'category' => ['required', Rule::enum(ProfileCategory::class)],

            'sector' => ['nullable', 'string', 'max:100'],
            'profession' => ['nullable', 'string', 'max:150'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'commune' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],

            'education_level' => ['nullable', Rule::enum(EducationLevel::class)],
            'institution' => ['nullable', 'string', 'max:150'],
            'field_of_study' => ['nullable', 'string', 'max:150'],
            'graduation_year' => ['nullable', 'integer', 'min:1950', 'max:'.date('Y')],

            'skills' => ['nullable', 'array', 'max:20'],
            'skills.*' => ['string', 'max:50'],
            'languages' => ['nullable', 'array', 'max:10'],
            'languages.*' => ['string', 'max:50'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'experiences' => ['nullable', 'array', 'max:10'],
            'experiences.*.title' => ['required_with:experiences', 'string', 'max:150'],
            'experiences.*.company' => ['nullable', 'string', 'max:150'],
            'experiences.*.from' => ['nullable', 'digits:4'],
            'experiences.*.to' => ['nullable', 'digits:4'],
            'experiences.*.description' => ['nullable', 'string', 'max:500'],

            'website' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],

            'show_email' => ['boolean'],
            'show_phone' => ['boolean'],
            'newsletter_subscribed' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.in' => 'La catégorie choisie est invalide.',
            'education_level.in' => 'Le niveau d\'études choisi est invalide.',
            'website.url' => 'L\'URL du site web est invalide.',
            'linkedin.url' => 'L\'URL LinkedIn est invalide.',
        ];
    }
}
