<?php

namespace App\Models;

use App\Models\BaseModel as Model;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'teacher_code',
        'first_name',
        'last_name',
        'gender',
        'phone',
        'email',
        'address',
        'subject_specialty',
        'hire_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
