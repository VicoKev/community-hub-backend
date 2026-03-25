<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /** @use HasFactory<\Database\Factories\PermissionFactory> */
    use HasFactory, HasUuid;

    protected $primaryKey = 'uuid';
}
