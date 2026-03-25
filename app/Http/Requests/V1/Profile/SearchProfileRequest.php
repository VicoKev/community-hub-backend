<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Profile;

use App\Enums\EducationLevel;
use App\Enums\ProfileCategory;
use App\Enums\ProfileStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SearchProfileRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::enum(ProfileCategory::class)],
            'sector' => ['nullable', 'string', 'max:100'],
            'education_level' => ['nullable', Rule::enum(EducationLevel::class)],
            'city' => ['nullable', 'string', 'max:100'],
            'commune' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', Rule::enum(ProfileStatus::class)],
            'sort_by' => ['nullable', 'string', 'in:created_at,sector'],
            'sort_dir' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],

        ];
    }
}
