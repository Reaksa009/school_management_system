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

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'អ្នកគ្រប់គ្រង',
            'email' => 'admin@sms.test',
            'role' => 'admin',
            'phone' => '010 000 001',
            'password' => 'password',
        ]);

        $teacherUser = User::create([
            'name' => 'គ្រូ សុភា',
            'email' => 'teacher@sms.test',
            'role' => 'teacher',
            'phone' => '010 000 002',
            'password' => 'password',
        ]);

        $accountant = User::create([
            'name' => 'គណនេយ្យករ',
            'email' => 'accountant@sms.test',
            'role' => 'accountant',
            'phone' => '010 000 003',
            'password' => 'password',
        ]);

        $parent = User::create([
            'name' => 'អាណាព្យាបាល សុខា',
            'email' => 'parent@sms.test',
            'role' => 'student_parent',
            'phone' => '010 000 004',
            'password' => 'password',
        ]);

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'teacher_code' => 'T-001',
            'first_name' => 'សុភា',
            'last_name' => 'គ្រូ',
            'gender' => 'ប្រុស',
            'phone' => '010 000 002',
            'email' => 'teacher@sms.test',
            'address' => 'ភ្នំពេញ',
            'subject_specialty' => 'គណិតវិទ្យា',
            'hire_date' => now()->subYears(3),
            'status' => 'active',
        ]);

        $teacherTwo = Teacher::create([
            'teacher_code' => 'T-002',
            'first_name' => 'សុវណ្ណ',
            'last_name' => 'គ្រូ',
            'gender' => 'ស្រី',
            'phone' => '010 000 005',
            'email' => 'teacher2@sms.test',
            'address' => 'កណ្តាល',
            'subject_specialty' => 'ភាសាខ្មែរ',
            'hire_date' => now()->subYears(2),
            'status' => 'active',
        ]);

        $gradeSeven = ClassRoom::create([
            'name' => 'ថ្នាក់ទី ៧',
            'section' => 'A',
            'academic_year' => '2025-2026',
            'room' => 'R-101',
            'teacher_id' => $teacher->id,
            'description' => 'ថ្នាក់គំរូសម្រាប់ការសាកល្បងប្រព័ន្ធ',
        ]);

        $gradeEight = ClassRoom::create([
            'name' => 'ថ្នាក់ទី ៨',
            'section' => 'A',
            'academic_year' => '2025-2026',
            'room' => 'R-201',
            'teacher_id' => $teacherTwo->id,
        ]);

        $math = Subject::create([
            'class_id' => $gradeSeven->id,
            'teacher_id' => $teacher->id,
            'code' => 'MATH-7',
            'name' => 'គណិតវិទ្យា',
            'credit_hours' => 4,
        ]);

        $khmer = Subject::create([
            'class_id' => $gradeSeven->id,
            'teacher_id' => $teacherTwo->id,
            'code' => 'KHM-7',
            'name' => 'ភាសាខ្មែរ',
            'credit_hours' => 3,
        ]);

        Subject::create([
            'class_id' => $gradeEight->id,
            'teacher_id' => $teacher->id,
            'code' => 'SCI-8',
            'name' => 'វិទ្យាសាស្ត្រ',
            'credit_hours' => 3,
        ]);

        $student = Student::create([
            'user_id' => $parent->id,
            'class_id' => $gradeSeven->id,
            'student_code' => 'S-001',
            'first_name' => 'សុខា',
            'last_name' => 'សិស្ស',
            'gender' => 'ប្រុស',
            'date_of_birth' => now()->subYears(12),
            'phone' => '012 111 222',
            'email' => 'student@sms.test',
            'address' => 'ភ្នំពេញ',
            'parent_name' => 'អាណាព្យាបាល សុខា',
            'parent_phone' => '010 000 004',
            'enrollment_date' => now()->subMonths(8),
            'status' => 'active',
        ]);

        $studentTwo = Student::create([
            'class_id' => $gradeSeven->id,
            'student_code' => 'S-002',
            'first_name' => 'ស្រីនាង',
            'last_name' => 'សិស្ស',
            'gender' => 'ស្រី',
            'date_of_birth' => now()->subYears(13),
            'phone' => '012 222 333',
            'address' => 'ភ្នំពេញ',
            'parent_name' => 'អ្នកម្តាយ ស្រីនាង',
            'parent_phone' => '010 000 006',
            'enrollment_date' => now()->subMonths(8),
            'status' => 'active',
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'class_id' => $gradeSeven->id,
            'subject_id' => $math->id,
            'teacher_id' => $teacher->id,
            'attendance_date' => now()->toDateString(),
            'status' => 'present',
        ]);

        Attendance::create([
            'student_id' => $studentTwo->id,
            'class_id' => $gradeSeven->id,
            'subject_id' => $math->id,
            'teacher_id' => $teacher->id,
            'attendance_date' => now()->toDateString(),
            'status' => 'late',
            'note' => 'មកយឺត ១០ នាទី',
        ]);

        $exam = Exam::create([
            'class_id' => $gradeSeven->id,
            'subject_id' => $math->id,
            'title' => 'ប្រឡងខែ គណិតវិទ្យា',
            'term' => 'ឆមាសទី ១',
            'exam_date' => now()->subDays(7),
            'total_marks' => 100,
        ]);

        Score::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'marks' => 88,
            'grade' => 'B',
        ]);

        Score::create([
            'exam_id' => $exam->id,
            'student_id' => $studentTwo->id,
            'marks' => 93,
            'grade' => 'A',
        ]);

        Payment::create([
            'student_id' => $student->id,
            'recorded_by' => $accountant->id,
            'receipt_no' => 'REC-202606-001',
            'payment_date' => now()->toDateString(),
            'amount' => 120,
            'method' => 'cash',
            'fee_type' => 'tuition',
            'billing_month' => now()->format('Y-m'),
            'status' => 'paid',
            'verification_status' => 'verified',
            'verified_at' => now(),
            'confirmed_at' => now(),
        ]);

        Payment::create([
            'student_id' => $studentTwo->id,
            'recorded_by' => $admin->id,
            'receipt_no' => 'REC-202606-002',
            'payment_date' => now()->subDays(2)->toDateString(),
            'amount' => 80,
            'method' => 'bank',
            'fee_type' => 'tuition',
            'billing_month' => now()->format('Y-m'),
            'status' => 'partial',
            'verification_status' => 'manual_confirmed',
            'confirmed_at' => now()->subDays(2),
        ]);
    }
}
