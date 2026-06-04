<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'phone',
        'is_active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function teacherProfile()
    {
        return $this->hasOne(Teacher::class);
    }

    public function studentProfile()
    {
        return $this->hasOne(Student::class);
    }

    public function recordedPayments()
    {
        return $this->hasMany(Payment::class, 'recorded_by');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'generated_by');
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function roleLabel(): string
    {
        return [
            'admin' => 'អ្នកគ្រប់គ្រង',
            'teacher' => 'គ្រូ',
            'accountant' => 'គណនេយ្យករ',
            'student' => 'សិស្ស',
            'student_parent' => 'សិស្ស/អាណាព្យាបាល',
        ][$this->role] ?? $this->role;
    }
}
