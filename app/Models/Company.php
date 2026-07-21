<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'website',
        'logo',
        'address',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
    public static function generateCompanyCode(string $name): string
{
    $words = preg_split('/\s+/', trim($name));

    $baseCode = collect($words)
        ->filter()
        ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
        ->join('');

    $code = $baseCode;
    $counter = 1;

    while (self::where('code', $code)->exists()) {

        $code = $baseCode . $counter;

        $counter++;
    }

    return $code;
}
}