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

    public static function parseDateValue($value, bool $withTime = false): ?string
    {
        if ($value === null || $value === '' || $value === '?' || $value === '-') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $withTime ? $value->format('Y-m-d H:i:s') : $value->format('Y-m-d');
        }

        $trimmed = trim((string)$value);
        if ($trimmed === '' || $trimmed === '?' || $trimmed === '-') {
            return null;
        }

        try {
            $carbon = \Carbon\Carbon::parse($trimmed);
            return $withTime ? $carbon->format('Y-m-d H:i:s') : $carbon->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setBirthDateAttribute($value)
    {
        $this->attributes['birth_date'] = self::parseDateValue($value);
    }

    public function setIssueDateAttribute($value)
    {
        $this->attributes['issue_date'] = self::parseDateValue($value);
    }

    public function setValidUntilAttribute($value)
    {
        $this->attributes['valid_until'] = self::parseDateValue($value);
    }

    public function setStudyStartAttribute($value)
    {
        $this->attributes['study_start'] = self::parseDateValue($value);
    }

    public function setStudyEndAttribute($value)
    {
        $this->attributes['study_end'] = self::parseDateValue($value);
    }

    public function setNextLevelAdmissionDateAttribute($value)
    {
        $this->attributes['next_level_admission_date'] = self::parseDateValue($value);
    }

    public function setLastUpdateAttribute($value)
    {
        $this->attributes['last_update'] = self::parseDateValue($value, true);
    }
}

