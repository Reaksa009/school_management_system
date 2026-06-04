<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'type',
        'title',
        'filters',
        'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
        ];
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
