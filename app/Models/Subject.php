<?php

namespace App\Models;

use App\Models\BaseModel as Model;

class Subject extends Model
{
    protected $fillable = [
        'class_id',
        'teacher_id',
        'code',
        'name',
        'credit_hours',
        'description',
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
