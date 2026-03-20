<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the trait.
     */
    public static function bootHasUuid()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Indicates that the primary key is not auto-incremented.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Indicates that the primary key is a string (UUID).
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
