<?php

namespace App\Models;

use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Platform\Models\User as Authenticatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => "Користувача «{$this->name}» створено",
                'updated' => "Дані користувача «{$this->name}» оновлено",
                'deleted' => "Користувача «{$this->name}» видалено",
                default   => "Користувач «{$this->name}»: {$eventName}",
            });
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'degree_id',
        'debug_enabled'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions'          => 'array',
        'email_verified_at'    => 'datetime',
        'debug_enabled' => 'boolean',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
           'id'         => Where::class,
           'name'       => Like::class,
           'email'      => Like::class,
           'updated_at' => WhereDateStartEnd::class,
           'created_at' => WhereDateStartEnd::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'updated_at',
        'created_at',
    ];


    public function specialties()
    {
        return $this->hasMany(UserSpecialty::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }


    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'user_specialty_subjects')
            ->withPivot('user_specialty_id') // збережемо ще спеціальність
            ->withTimestamps();
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function degree()
    {
        return $this->belongsTo(Degree::class);
    }

}
