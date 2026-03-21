<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    use HasUuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user that owns the email verification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if the verification code is expired.
     */
    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    /**
     * Determine if the verification code is verified.
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Mark the verification as verified.
     */
    public function markAsVerified(): bool
    {
        return $this->forceFill([
            'verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Generate a new verification code.
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    }
}
