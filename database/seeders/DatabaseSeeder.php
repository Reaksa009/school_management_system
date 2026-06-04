<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Exam;
use App\Models\Payment;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@sms.test')],
            [
                'name' => env('SEED_ADMIN_NAME', 'System Administrator'),
                'role' => 'admin',
                'phone' => '010 000 001',
                'is_active' => true,
                'password' => env('SEED_ADMIN_PASSWORD', 'password'),
            ],
        );

        $teacherUser = User::updateOrCreate(
            ['email' => 'teacher@sms.test'],
            [
                'name' => 'Demo Teacher',
                'role' => 'teacher',
                'phone' => '010 000 002',
                'is_active' => true,
                'password' => 'password',
            ],
        );

        $accountant = User::updateOrCreate(
            ['email' => 'accountant@sms.test'],
            [
                'name' => 'Demo Accountant',
                'role' => 'accountant',
                'phone' => '010 000 003',
                'is_active' => true,
                'password' => 'password',
            ],
        );

        $parent = User::updateOrCreate(
            ['email' => 'parent@sms.test'],
            [
                'name' => 'Demo Parent',
                'role' => 'student_parent',
                'phone' => '010 000 004',
                'is_active' => true,
                'password' => 'password',
            ],
        );

        $teacher = Teacher::updateOrCreate(
            ['teacher_code' => 'T-001'],
            [
                'user_id' => $teacherUser->getKey(),
                'first_name' => 'Sotha',
                'last_name' => 'Teacher',
                'gender' => 'male',
                'phone' => '010 000 002',
                'email' => 'teacher@sms.test',
                'address' => 'Phnom Penh',
                'subject_specialty' => 'Mathematics',
                'hire_date' => now()->subYears(3),
                'status' => 'active',
            ],
        );

        $teacherTwo = Teacher::updateOrCreate(
            ['teacher_code' => 'T-002'],
            [
                'first_name' => 'Sovann',
                'last_name' => 'Teacher',
                'gender' => 'female',
                'phone' => '010 000 005',
                'email' => 'teacher2@sms.test',
                'address' => 'Kandal',
                'subject_specialty' => 'Khmer',
                'hire_date' => now()->subYears(2),
                'status' => 'active',
            ],
        );

        $gradeSeven = ClassRoom::updateOrCreate(
            ['name' => 'Grade 7', 'section' => 'A', 'academic_year' => '2025-2026'],
            [
                'room' => 'R-101',
                'teacher_id' => $teacher->getKey(),
                'description' => 'Demo class for the school management system.',
            ],
        );

        $gradeEight = ClassRoom::updateOrCreate(
            ['name' => 'Grade 8', 'section' => 'A', 'academic_year' => '2025-2026'],
            [
                'room' => 'R-201',
                'teacher_id' => $teacherTwo->getKey(),
                'description' => 'Second demo class.',
            ],
        );

        $math = Subject::updateOrCreate(
            ['code' => 'MATH-7'],
            [
                'class_id' => $gradeSeven->getKey(),
                'teacher_id' => $teacher->getKey(),
                'name' => 'Mathematics',
                'credit_hours' => 4,
            ],
        );

        $khmer = Subject::updateOrCreate(
            ['code' => 'KHM-7'],
            [
                'class_id' => $gradeSeven->getKey(),
                'teacher_id' => $teacherTwo->getKey(),
                'name' => 'Khmer',
                'credit_hours' => 3,
            ],
        );

        Subject::updateOrCreate(
            ['code' => 'SCI-8'],
            [
                'class_id' => $gradeEight->getKey(),
                'teacher_id' => $teacher->getKey(),
                'name' => 'Science',
                'credit_hours' => 3,
            ],
        );

        $student = Student::updateOrCreate(
            ['student_code' => 'S-001'],
            [
                'user_id' => $parent->getKey(),
                'class_id' => $gradeSeven->getKey(),
                'first_name' => 'Sokha',
                'last_name' => 'Student',
                'gender' => 'male',
                'date_of_birth' => now()->subYears(12),
                'phone' => '012 111 222',
                'email' => 'student@sms.test',
                'address' => 'Phnom Penh',
                'parent_name' => 'Demo Parent',
                'parent_phone' => '010 000 004',
                'enrollment_date' => now()->subMonths(8),
                'status' => 'active',
            ],
        );

        $studentTwo = Student::updateOrCreate(
            ['student_code' => 'S-002'],
            [
                'class_id' => $gradeSeven->getKey(),
                'first_name' => 'Sreyneang',
                'last_name' => 'Student',
                'gender' => 'female',
                'date_of_birth' => now()->subYears(13),
                'phone' => '012 222 333',
                'address' => 'Phnom Penh',
                'parent_name' => 'Demo Parent 2',
                'parent_phone' => '010 000 006',
                'enrollment_date' => now()->subMonths(8),
                'status' => 'active',
            ],
        );

        Attendance::updateOrCreate(
            [
                'student_id' => $student->getKey(),
                'class_id' => $gradeSeven->getKey(),
                'subject_id' => $math->getKey(),
                'attendance_date' => now()->toDateString(),
            ],
            [
                'teacher_id' => $teacher->getKey(),
                'status' => 'present',
            ],
        );

        Attendance::updateOrCreate(
            [
                'student_id' => $studentTwo->getKey(),
                'class_id' => $gradeSeven->getKey(),
                'subject_id' => $khmer->getKey(),
                'attendance_date' => now()->toDateString(),
            ],
            [
                'teacher_id' => $teacherTwo->getKey(),
                'status' => 'late',
                'note' => 'Arrived 10 minutes late.',
            ],
        );

        $exam = Exam::updateOrCreate(
            ['title' => 'Monthly Mathematics Exam', 'term' => 'Term 1'],
            [
                'class_id' => $gradeSeven->getKey(),
                'subject_id' => $math->getKey(),
                'exam_date' => now()->subDays(7),
                'total_marks' => 100,
            ],
        );

        Score::updateOrCreate(
            ['exam_id' => $exam->getKey(), 'student_id' => $student->getKey()],
            ['marks' => 88, 'grade' => 'B'],
        );

        Score::updateOrCreate(
            ['exam_id' => $exam->getKey(), 'student_id' => $studentTwo->getKey()],
            ['marks' => 93, 'grade' => 'A'],
        );

        Payment::updateOrCreate(
            ['receipt_no' => 'REC-202606-001'],
            [
                'student_id' => $student->getKey(),
                'recorded_by' => $accountant->getKey(),
                'payment_date' => now()->toDateString(),
                'amount' => 120,
                'method' => 'cash',
                'fee_type' => 'tuition',
                'billing_month' => now()->format('Y-m'),
                'status' => 'paid',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'confirmed_at' => now(),
            ],
        );

        Payment::updateOrCreate(
            ['receipt_no' => 'REC-202606-002'],
            [
                'student_id' => $studentTwo->getKey(),
                'recorded_by' => $admin->getKey(),
                'payment_date' => now()->subDays(2)->toDateString(),
                'amount' => 80,
                'method' => 'bank',
                'fee_type' => 'tuition',
                'billing_month' => now()->format('Y-m'),
                'status' => 'partial',
                'verification_status' => 'manual_confirmed',
                'confirmed_at' => now()->subDays(2),
            ],
        );
    }
}
