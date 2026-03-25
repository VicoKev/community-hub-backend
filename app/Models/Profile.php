<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EducationLevel;
use App\Enums\ProfileCategory;
use App\Enums\ProfileStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'birth_date',
        'bio',
        'category',
        'sector',
        'profession',
        'company_name',
        'city',
        'commune',
        'address',
        'education_level',
        'institution',
        'field_of_study',
        'graduation_year',
        'skills',
        'languages',
        'years_of_experience',
        'experiences',
        'website',
        'linkedin',
        'show_email',
        'show_phone',
        'status',
        'validated_by',
        'validated_at',
        'rejection_reason',
        'newsletter_subscribed',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'category' => ProfileCategory::class,
        'education_level' => EducationLevel::class,
        'skills' => 'array',
        'languages' => 'array',
        'experiences' => 'array',
        'show_email' => 'boolean',
        'show_phone' => 'boolean',
        'status' => ProfileStatus::class,
        'validated_at' => 'datetime',
        'newsletter_subscribed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->withResponsiveImages();

        $this->addMediaCollection('cv')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Miniature pour l'avatar
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('avatar');

        // Version pour l'affichage dans le profil
        $this->addMediaConversion('medium')
            ->width(400)
            ->height(400)
            ->performOnCollections('avatar');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', ProfileStatus::APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', ProfileStatus::PENDING);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term): void {
            $q->where('profession', 'like', "%{$term}%")
                ->orWhere('company_name', 'like', "%{$term}%")
                ->orWhere('sector', 'like', "%{$term}%")
                ->orWhereJsonContains('skills', $term);
        });
    }

    public function isPending(): bool
    {
        return $this->status === ProfileStatus::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === ProfileStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === ProfileStatus::REJECTED;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('avatar', 'medium') ?: null;
    }

    public function getCategoryLabelAttribute(): string
    {
        return $this->category?->label() ?? '';
    }
}
