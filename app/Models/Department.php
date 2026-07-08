<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass Assignable Fields
     */
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'description',
        'status',
        'created_by',
        'updated_by',
        'auto_deactivated',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'status' => 'boolean',
        'auto_deactivated' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Department belongs to Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Department has many Users
     * (Will be used in User Module)
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Created By
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Updated By
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Active Departments
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Filter by Company
     */
    public function scopeCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}