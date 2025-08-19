<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
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
        'department',
        'control_type',
        'max_min_students'

    ];
}
