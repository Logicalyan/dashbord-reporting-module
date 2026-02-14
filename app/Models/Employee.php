<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'position',
        'is_active',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }
}
