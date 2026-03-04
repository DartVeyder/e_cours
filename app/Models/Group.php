<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class Group extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;

    protected $fillable = [
        'name',
        'department_id',
        'degree_id',
        'semester_count',
    ];

    protected $allowedSorts = [
        'name',
        'department_id',
        'degree_id',
    ];

    protected $allowedFilters = [
        'name'          => Like::class,
        'department_id' => Where::class,
        'degree_id'     => Where::class,
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
