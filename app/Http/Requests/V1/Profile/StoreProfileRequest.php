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
            'graduation_year' => ['nullable', 'integer', 'min:1950', 'max:' . date('Y')],

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
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'phone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',

            'birth_date.date' => 'La date de naissance doit être au format valide.',
            'birth_date.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',

            'bio.string' => 'La biographie doit être une chaîne de caractères.',
            'bio.max' => 'La biographie ne peut pas dépasser 2000 caractères.',

            'category.required' => 'La catégorie est requise.',
            'category.in' => 'La catégorie choisie est invalide.',
            'category.enum' => 'La catégorie choisie est invalide.',

            'sector.string' => 'Le secteur doit être une chaîne de caractères.',
            'sector.max' => 'Le secteur ne peut pas dépasser 100 caractères.',

            'profession.string' => 'La profession doit être une chaîne de caractères.',
            'profession.max' => 'La profession ne peut pas dépasser 150 caractères.',

            'company_name.string' => 'Le nom de l\'entreprise doit être une chaîne de caractères.',
            'company_name.max' => 'Le nom de l\'entreprise ne peut pas dépasser 150 caractères.',

            'city.string' => 'La ville doit être une chaîne de caractères.',
            'city.max' => 'La ville ne peut pas dépasser 100 caractères.',

            'commune.string' => 'La commune doit être une chaîne de caractères.',
            'commune.max' => 'La commune ne peut pas dépasser 100 caractères.',

            'address.string' => 'L\'adresse doit être une chaîne de caractères.',
            'address.max' => 'L\'adresse ne peut pas dépasser 255 caractères.',

            'education_level.in' => 'Le niveau d\'études choisi est invalide.',
            'education_level.enum' => 'Le niveau d\'études choisi est invalide.',

            'institution.string' => 'L\'établissement doit être une chaîne de caractères.',
            'institution.max' => 'L\'établissement ne peut pas dépasser 150 caractères.',

            'field_of_study.string' => 'Le domaine d\'études doit être une chaîne de caractères.',
            'field_of_study.max' => 'Le domaine d\'études ne peut pas dépasser 150 caractères.',

            'graduation_year.integer' => 'L\'année de diplôme doit être un nombre.',
            'graduation_year.min' => 'L\'année de diplôme doit être supérieure ou égale à 1950.',
            'graduation_year.max' => 'L\'année de diplôme ne peut pas être dans le futur.',

            'skills.array' => 'Les compétences doivent être fournies sous forme de liste.',
            'skills.max' => 'Vous ne pouvez pas ajouter plus de 20 compétences.',
            'skills.*.string' => 'Chaque compétence doit être une chaîne de caractères.',
            'skills.*.max' => 'Chaque compétence ne peut pas dépasser 50 caractères.',

            'languages.array' => 'Les langues doivent être fournies sous forme de liste.',
            'languages.max' => 'Vous ne pouvez pas ajouter plus de 10 langues.',
            'languages.*.string' => 'Chaque langue doit être une chaîne de caractères.',
            'languages.*.max' => 'Chaque langue ne peut pas dépasser 50 caractères.',

            'years_of_experience.integer' => 'Les années d\'expérience doivent être un nombre.',
            'years_of_experience.min' => 'Les années d\'expérience doivent être positives.',
            'years_of_experience.max' => 'Les années d\'expérience ne peuvent pas dépasser 60.',

            'experiences.array' => 'Les expériences doivent être fournies sous forme de liste.',
            'experiences.max' => 'Vous ne pouvez pas ajouter plus de 10 expériences.',

            'experiences.*.title.required_with' => 'Le titre de l\'expérience est requis lorsque vous ajoutez une expérience.',
            'experiences.*.title.string' => 'Le titre de l\'expérience doit être une chaîne de caractères.',
            'experiences.*.title.max' => 'Le titre de l\'expérience ne peut pas dépasser 150 caractères.',

            'experiences.*.company.string' => 'Le nom de l\'entreprise doit être une chaîne de caractères.',
            'experiences.*.company.max' => 'Le nom de l\'entreprise ne peut pas dépasser 150 caractères.',

            'experiences.*.from.digits' => 'L\'année de début doit être un nombre à 4 chiffres.',
            'experiences.*.to.digits' => 'L\'année de fin doit être un nombre à 4 chiffres.',
            'experiences.*.description.string' => 'La description de l\'expérience doit être une chaîne de caractères.',
            'experiences.*.description.max' => 'La description de l\'expérience ne peut pas dépasser 500 caractères.',

            'website.url' => 'L\'URL du site web est invalide.',
            'website.max' => 'L\'URL du site web ne peut pas dépasser 255 caractères.',

            'linkedin.url' => 'L\'URL LinkedIn est invalide.',
            'linkedin.max' => 'L\'URL LinkedIn ne peut pas dépasser 255 caractères.',

            'show_email.boolean' => 'Le champ afficher l\'e-mail doit être vrai ou faux.',
            'show_phone.boolean' => 'Le champ afficher le téléphone doit être vrai ou faux.',
            'newsletter_subscribed.boolean' => 'Le champ abonnement à la newsletter doit être vrai ou faux.',
        ];
    }
}
