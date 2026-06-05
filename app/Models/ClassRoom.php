<?php

namespace App\Models;

use App\Models\BaseModel as Model;

class ClassRoom extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'section',
        'academic_year',
        'room',
        'teacher_id',
        'description',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'class_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'class_id');
    }
}
