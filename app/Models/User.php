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

    'employee_id',

    'name',
    'email',
    'password',

    'company_id',
    'department_id',

    'designation',
    'phone',
    'gender',
    'dob',
    'joining_date',
    'emergency_contact',
    'address',

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

        'dob' => 'date',
        'joining_date' => 'date',
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

    public static function generateEmployeeId(
    int $companyId
): string
{
    $company = Company::findOrFail($companyId);

    $prefix = $company->code;

    $lastUser = self::where(
            'company_id',
            $companyId
        )
        ->whereNotNull('employee_id')
        ->latest('id')
        ->first();

    if (!$lastUser) {

        return $prefix . '-0001';

    }

    $lastNumber = (int) substr(
        $lastUser->employee_id,
        strrpos(
            $lastUser->employee_id,
            '-'
        ) + 1
    );

    return $prefix . '-' .
        str_pad(
            $lastNumber + 1,
            4,
            '0',
            STR_PAD_LEFT
        );
}

}