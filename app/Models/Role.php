<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{

    protected $fillable = [
        'name',
        'guard_name',
        'company_id',
        'is_system',
    ];


    protected $casts = [
        'is_system' => 'boolean',
    ];


    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }


    public function isSystemRole(): bool
    {
        return $this->is_system === true;
    }


    public function isCustomRole(): bool
    {
        return $this->company_id !== null;
    }

}