<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class Subject extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;

    protected $guarded = [];

    protected $allowedSorts = [
        'id',
        'name',
        'is_selected',
        'department',
        'control_type',
        'max_min_students',
        'education_level',
        'users_count'

    ];

    protected $allowedFilters = [
        'id'            => Where::class,
        'name'       => Like::class,
        'department' => Where::class,
        'education_level' => Where::class,
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_specialty_subjects')
            ->withPivot('user_specialty_id', 'is_student_choice', 'semester')
            ->withTimestamps();
    }

    public function userSpecialties()
    {
        return $this->belongsToMany(UserSpecialty::class, 'user_specialty_subjects', 'subject_id', 'user_specialty_id');
    }
}
