<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupSemesterLimit extends Model
{
    use HasFactory;

    protected $table = 'group_semester_limits';

    protected $fillable = [
        'group_id',
        'semester',
        'max_subjects',
    ];

    /**
     * Зв’язок з групою
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
