<?php

namespace App\Models;

use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityLogFactory> */
    use HasFactory, HasUuid;

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id',
        'properties', 'ip_address', 'user_agent', 'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'properties' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $log) => $log->created_at ??= now());
    }
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
 
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }
 
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
