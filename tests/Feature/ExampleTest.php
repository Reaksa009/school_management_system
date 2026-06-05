<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/dashboard');
    }

    public function test_admin_can_view_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('ផ្ទាំងគ្រប់គ្រង');
    }

    public function test_admin_module_pages_render(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@sms.test')->firstOrFail();

        foreach ([
            'students.index',
            'students.import',
            'teachers.index',
            'teachers.import',
            'classes.index',
            'subjects.index',
            'attendances.index',
            'exams.index',
            'scores.index',
            'payments.index',
            'payments.khqr.create',
            'reports.index',
            'users.index',
        ] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    public function test_admin_can_import_students_from_csv(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@sms.test')->firstOrFail();
        $csv = implode("\n", [
            'student_code,first_name,last_name,class_name,gender,phone,status',
            'S-IMPORT-1,Dara,Sok,ថ្នាក់ទី ៧,Male,012345678,active',
        ]);

        $response = $this->actingAs($admin)->post(route('students.import.store'), [
            'csv_file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ]);

        $response->assertRedirect(route('students.index'));

        $student = Student::where('student_code', 'S-IMPORT-1')->first();
        $this->assertNotNull($student);
        $this->assertSame('Dara', $student->first_name);
        $this->assertSame('ថ្នាក់ទី ៧', $student->classRoom?->name);
    }

    public function test_admin_can_import_teachers_from_csv(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@sms.test')->firstOrFail();
        $csv = implode("\n", [
            'teacher_code,first_name,last_name,gender,phone,subject_specialty,status',
            'T-IMPORT-1,Sophea,Teacher,Female,012345678,Information Technology,active',
        ]);

        $response = $this->actingAs($admin)->post(route('teachers.import.store'), [
            'csv_file' => UploadedFile::fake()->createWithContent('teachers.csv', $csv),
        ]);

        $response->assertRedirect(route('teachers.index'));

        $teacher = Teacher::where('teacher_code', 'T-IMPORT-1')->first();
        $this->assertNotNull($teacher);
        $this->assertSame('Sophea', $teacher->first_name);
        $this->assertSame('Information Technology', $teacher->subject_specialty);
    }

    public function test_student_can_request_account_and_wait_for_admin_approval(): void
    {
        $this->seed();

        $response = $this->post(route('register.store'), [
            'role' => 'student',
            'profile_code' => 'S-002',
            'name' => 'Student Srey Nita',
            'email' => 'srey.nita@student.test',
            'phone' => '011222333',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login'));

        $user = User::where('email', 'srey.nita@student.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('student', $user->role);
        $this->assertFalse($user->is_active);
        $this->assertSame($user->id, Student::where('student_code', 'S-002')->value('user_id'));

        $this->post(route('login.store'), [
            'email' => 'srey.nita@student.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $user->update(['is_active' => true]);

        $this->actingAs($user)
            ->get(route('students.index'))
            ->assertOk()
            ->assertSee('S-002')
            ->assertDontSee('S-001');
    }

    public function test_teacher_can_request_account_and_wait_for_admin_approval(): void
    {
        $this->seed();

        $response = $this->post(route('register.store'), [
            'role' => 'teacher',
            'profile_code' => 'T-002',
            'name' => 'Teacher Sovan',
            'email' => 'sovan.teacher@test.local',
            'phone' => '011222333',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login'));

        $user = User::where('email', 'sovan.teacher@test.local')->first();
        $this->assertNotNull($user);
        $this->assertSame('teacher', $user->role);
        $this->assertFalse($user->is_active);
        $this->assertSame($user->id, Teacher::where('teacher_code', 'T-002')->value('user_id'));
    }

    public function test_parent_can_generate_khqr_tuition_payment(): void
    {
        $this->seed();

        config([
            'khqr.provider' => 'khqr',
            'khqr.bakong_account_id' => 'vuthy_reaksa@bkrt',
            'khqr.account_name' => 'VUTHY REAKSA',
            'khqr.merchant_city' => 'PHNOM PENH',
            'khqr.currency' => 'USD',
        ]);

        $parent = User::where('email', 'parent@sms.test')->firstOrFail();
        $student = Student::where('user_id', $parent->id)->firstOrFail();

        $response = $this->actingAs($parent)->post(route('payments.khqr.store'), [
            'student_id' => $student->id,
            'amount' => '25.50',
            'billing_month' => '2026-06',
        ]);

        $payment = Payment::where('student_id', $student->id)
            ->where('method', 'khqr')
            ->first();

        $response->assertRedirect(route('payments.show', $payment));
        $this->assertSame('pending', $payment->status);
        $this->assertNotEmpty($payment->khqr_payload);
        $this->assertNotEmpty($payment->khqr_md5);
        $this->assertStringContainsString('9934', $payment->khqr_payload);
        $this->assertNotEmpty(data_get($payment->meta, 'khqr.raw_payload.expiration_timestamp'));
    }

    public function test_paid_khqr_check_returns_verified_json(): void
    {
        $this->seed();

        $parent = User::where('email', 'parent@sms.test')->firstOrFail();
        $student = Student::where('user_id', $parent->id)->firstOrFail();

        $payment = Payment::create([
            'student_id' => $student->id,
            'recorded_by' => $parent->id,
            'provider' => 'khqr',
            'receipt_no' => 'KHQR-TEST-PAID',
            'payment_date' => now()->toDateString(),
            'amount' => 25.50,
            'method' => 'khqr',
            'fee_type' => 'tuition',
            'billing_month' => '2026-06',
            'status' => 'paid',
            'transaction_id' => 'TXN-PAID-1',
            'verification_status' => 'verified',
            'verified_at' => now(),
            'confirmed_at' => now(),
        ]);

        $this->actingAs($parent)
            ->postJson(route('payments.khqr.check', $payment))
            ->assertOk()
            ->assertJson([
                'status' => 'paid',
                'verification_status' => 'verified',
                'transaction_id' => 'TXN-PAID-1',
            ]);
    }
}
