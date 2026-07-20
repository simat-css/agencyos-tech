<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;


    protected $fillable = [
    'name',
    'email',
    'password',
    'company_id',
    'department_id',
    'profile_photo',
    'status',
    'created_by',
    'updated_by',
];



    protected $hidden = [
        'password',
        'remember_token',
    ];



    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }


/*
|--------------------------------------------------------------------------
| Created By
|--------------------------------------------------------------------------
*/

public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

/*
|--------------------------------------------------------------------------
| Updated By
|--------------------------------------------------------------------------
*/

public function updater()
{
    return $this->belongsTo(User::class, 'updated_by');
}

    /*
    |--------------------------------------------------------------------------
    | Company Relationship
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }



    /*
    |--------------------------------------------------------------------------
    | Department Relationship
    |--------------------------------------------------------------------------
    */

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

}