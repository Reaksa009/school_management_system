<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassRoomController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

if (env('SEED_TOKEN')) {
    Route::post('/setup/db-check', function (Request $request) {
        abort_unless(hash_equals((string) env('SEED_TOKEN'), (string) $request->bearerToken()), 404);

        try {
            $client = new \MongoDB\Client((string) env('DB_URI', env('MONGODB_URI')), [
                'connectTimeoutMS' => 5000,
                'serverSelectionTimeoutMS' => 5000,
            ]);
            $client->selectDatabase((string) env('DB_DATABASE', 'school_management_system'))
                ->command(['ping' => 1])
                ->toArray();
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => 'MongoDB check failed.',
                'error' => $exception::class,
                'detail' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'MongoDB connection ok.',
            'database' => env('DB_DATABASE', 'school_management_system'),
        ]);
    });

    Route::post('/setup/seed', function (Request $request) {
        abort_unless(hash_equals((string) env('SEED_TOKEN'), (string) $request->bearerToken()), 404);

        try {
            Artisan::call('db:seed', ['--force' => true]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Database seed failed.',
                'error' => $exception::class,
                'detail' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Database seeded.',
            'output' => trim(Artisan::output()),
        ]);
    });
}

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:admin,teacher,student,student_parent')->group(function () {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
        Route::get('/students/import', [StudentController::class, 'importForm'])->name('students.import');
        Route::post('/students/import', [StudentController::class, 'import'])->name('students.import.store');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
        Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
        Route::get('/classes', [ClassRoomController::class, 'index'])->name('classes.index');
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('/attendances/create', [AttendanceController::class, 'create'])->name('attendances.create');
        Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');
        Route::get('/attendances/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendances.edit');
        Route::put('/attendances/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');
        Route::delete('/attendances/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');
        Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
        Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
        Route::get('/exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
        Route::put('/exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
        Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
        Route::get('/scores', [ScoreController::class, 'index'])->name('scores.index');
        Route::get('/scores/{exam}/edit', [ScoreController::class, 'edit'])->name('scores.edit');
        Route::put('/scores/{exam}', [ScoreController::class, 'update'])->name('scores.update');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/teachers/import', [TeacherController::class, 'importForm'])->name('teachers.import');
        Route::post('/teachers/import', [TeacherController::class, 'import'])->name('teachers.import.store');
        Route::get('/teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
        Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

        Route::get('/classes/create', [ClassRoomController::class, 'create'])->name('classes.create');
        Route::post('/classes', [ClassRoomController::class, 'store'])->name('classes.store');
        Route::get('/classes/{classRoom}/edit', [ClassRoomController::class, 'edit'])->name('classes.edit');
        Route::put('/classes/{classRoom}', [ClassRoomController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{classRoom}', [ClassRoomController::class, 'destroy'])->name('classes.destroy');

        Route::get('/subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
        Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

        Route::resource('users', UserController::class)->except('show');
    });

    Route::middleware('role:admin,accountant,student,student_parent')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments/{payment}/khqr/check', [PaymentController::class, 'checkKhqr'])->name('payments.khqr.check');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    });

    Route::middleware('role:admin,accountant,student_parent')->group(function () {
        Route::get('/payments/khqr/create', [PaymentController::class, 'createKhqr'])->name('payments.khqr.create');
        Route::post('/payments/khqr', [PaymentController::class, 'storeKhqr'])->name('payments.khqr.store');
    });

    Route::middleware('role:admin,accountant')->group(function () {
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    Route::middleware('role:admin,teacher,accountant,student,student_parent')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{type}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('/reports/{type}/export/{format}', [ReportController::class, 'export'])->name('reports.export');
    });
});
