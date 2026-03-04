<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class UserSpecialty extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['deleted_at' => 'datetime'];

    protected $allowedSorts = [
        'full_name',
        'degree',
        'department',
        'specialty',
        'education_program',
        'gender',
        'study_form',
        'group_name',
        'email',
        'card_id',
        'subjects_count',

    ];

    protected $allowedFilters = [
        'full_name'            => Like::class,
        'email'            => Like::class,
        'card_id'            => Like::class,
        'degree'            => Where::class,
        'department'            => Where::class,
        'specialty'            => Where::class,
        'education_program'  => Where::class,
        'gender'             => Where::class,
        'study_form'         => Where::class,
        'group_name'              => Where::class,
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'user_specialty_subjects')
            ->withPivot(['semester', 'is_student_choice'])
            ->orderBy('name');
    }

    public function group(){
        return $this->belongsTo(Group::class);
    }
}
