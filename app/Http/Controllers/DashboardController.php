<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Payment;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        if (in_array($user->role, ['student', 'student_parent'], true)) {
            $student = Student::with([
                'classRoom',
                'attendances.subject',
                'scores.exam.subject',
                'payments',
            ])->where('user_id', $user->id)->first();

            return view('dashboard', [
                'student' => $student,
                'latestAttendances' => $student?->attendances()->with('subject')->latest('attendance_date')->limit(6)->get() ?? collect(),
                'latestScores' => $student?->scores()->with('exam.subject')->latest()->limit(6)->get() ?? collect(),
                'latestPayments' => $student?->payments()->latest('payment_date')->limit(6)->get() ?? collect(),
            ]);
        }

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $monthlyIncome = Payment::where('status', 'paid')
            ->where('payment_date', '>=', $monthStart)
            ->where('payment_date', '<=', $monthEnd)
            ->get()
            ->sum(fn (Payment $payment) => (float) $payment->amount);

        $attendanceToday = Attendance::where('attendance_date', '>=', $today->copy()->startOfDay())
            ->where('attendance_date', '<=', $today->copy()->endOfDay())
            ->get()
            ->groupBy('status')
            ->map->count();

        return view('dashboard', [
            'stats' => [
                'students' => Student::count(),
                'teachers' => Teacher::count(),
                'classes' => ClassRoom::count(),
                'subjects' => Subject::count(),
                'users' => User::count(),
                'monthly_income' => $monthlyIncome,
            ],
            'attendanceToday' => $attendanceToday,
            'latestPayments' => Payment::with('student')->latest('payment_date')->limit(5)->get(),
            'latestAttendances' => Attendance::with(['student', 'classRoom', 'subject'])->latest('attendance_date')->limit(5)->get(),
            'latestScores' => Score::with(['student', 'exam.subject'])->latest()->limit(5)->get(),
        ]);
    }
}
