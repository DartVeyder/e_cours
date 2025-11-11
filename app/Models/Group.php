<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department_id',
        'degree_id',
        'semester_count',
    ];


    public function specialties()
    {
        $this->hasMany(UserSpecialty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function degree()
    {
        return $this->belongsTo(Degree::class);
    }

    public function semesterLimits()
    {
        return $this->hasMany(GroupSemesterLimit::class);
    }
}
