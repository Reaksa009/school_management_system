<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'class_id',
        'subject_id',
        'title',
        'term',
        'exam_date',
        'total_marks',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'total_marks' => 'decimal:2',
        ];
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }
}
