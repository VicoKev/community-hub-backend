<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Profile;

use App\Enums\EducationLevel;
use App\Enums\ProfileCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProfileRequest extends FormRequest
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
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'birth_date' => ['sometimes', 'nullable', 'date', 'before:today'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'category' => ['sometimes', Rule::enum(ProfileCategory::class)],
            'sector' => ['sometimes', 'nullable', 'string', 'max:100'],
            'profession' => ['sometimes', 'nullable', 'string', 'max:150'],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:150'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'commune' => ['sometimes', 'nullable', 'string', 'max:100'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'education_level' => ['sometimes', 'nullable', Rule::enum(EducationLevel::class)],
            'institution' => ['sometimes', 'nullable', 'string', 'max:150'],
            'field_of_study' => ['sometimes', 'nullable', 'string', 'max:150'],
            'graduation_year' => ['sometimes', 'nullable', 'integer', 'min:1950', 'max:'.date('Y')],
            'skills' => ['sometimes', 'nullable', 'array', 'max:20'],
            'skills.*' => ['string', 'max:50'],
            'languages' => ['sometimes', 'nullable', 'array', 'max:10'],
            'languages.*' => ['string', 'max:50'],
            'years_of_experience' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:60'],
            'experiences' => ['sometimes', 'nullable', 'array', 'max:10'],
            'experiences.*.title' => ['required_with:experiences', 'string', 'max:150'],
            'experiences.*.company' => ['nullable', 'string', 'max:150'],
            'experiences.*.from' => ['nullable', 'digits:4'],
            'experiences.*.to' => ['nullable', 'digits:4'],
            'experiences.*.description' => ['nullable', 'string', 'max:500'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'linkedin' => ['sometimes', 'nullable', 'url', 'max:255'],
            'show_email' => ['sometimes', 'boolean'],
            'show_phone' => ['sometimes', 'boolean'],
            'newsletter_subscribed' => ['sometimes', 'boolean'],
        ];
    }
}
