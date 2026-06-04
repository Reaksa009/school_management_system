<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'subject_id',
        'teacher_id',
        'attendance_date',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function statusLabel(): string
    {
        return [
            'present' => 'មានវត្តមាន',
            'absent' => 'អវត្តមាន',
            'late' => 'មកយឺត',
            'excused' => 'សុំច្បាប់',
        ][$this->status] ?? $this->status;
    }
}
