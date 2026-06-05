<?php

namespace App\Models;

use App\Models\BaseModel as Model;

class Score extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'marks',
        'grade',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'marks' => 'decimal:2',
        ];
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
