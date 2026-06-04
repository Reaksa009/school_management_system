<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'student_id',
        'recorded_by',
        'provider',
        'receipt_no',
        'payment_date',
        'amount',
        'method',
        'fee_type',
        'billing_month',
        'status',
        'transaction_id',
        'verification_status',
        'verification_error',
        'verified_at',
        'khqr_payload',
        'khqr_md5',
        'khqr_expires_at',
        'submitted_at',
        'confirmed_at',
        'meta',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'verified_at' => 'datetime',
            'khqr_expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isKhqr(): bool
    {
        return $this->method === 'khqr' || $this->provider === 'khqr';
    }
}
