<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class UserSpecialty extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;

    protected $guarded = [];

    protected $allowedSorts = [
        'full_name',
        'degree',
        'department',
        'specialty',
        'education_program',
        'gender',
        'study_form',
        'group',

    ];

    protected $allowedFilters = [
        'full_name'            => Like::class,
        'degree'            => Where::class,
        'department'            => Where::class,
        'specialty'            => Where::class,
        'education_program'  => Where::class,
        'gender'             => Where::class,
        'study_form'         => Where::class,
        'group'              => Where::class,
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
