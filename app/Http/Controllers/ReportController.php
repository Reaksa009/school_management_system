<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Report;
use App\Models\Score;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index', [
            'reports' => $this->availableReports(),
        ]);
    }

    public function show(Request $request, string $type)
    {
        $report = $this->buildReport($type, $request);

        Report::create([
            'type' => $type,
            'title' => $report['title'],
            'filters' => $request->query(),
            'generated_by' => auth()->id(),
        ]);

        return view('reports.show', $report + [
            'type' => $type,
            'reports' => $this->availableReports(),
        ]);
    }

    public function export(Request $request, string $type, string $format)
    {
        $report = $this->buildReport($type, $request);

        if ($format === 'pdf') {
            return view('reports.print', $report);
        }

        abort_unless($format === 'excel', 404);

        $rows = collect([$report['headers']])->merge($report['rows']);
        $csv = chr(239).chr(187).chr(191).$rows->map(function ($row) {
            return collect($row)->map(function ($value) {
                $value = str_replace('"', '""', (string) $value);

                return "\"{$value}\"";
            })->implode(',');
        })->implode("\r\n");

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.Str::slug($report['title']).'.csv"',
        ]);
    }

    private function availableReports(): array
    {
        $role = auth()->user()->role;

        $reports = [
            'students' => 'របាយការណ៍សិស្ស',
            'teachers' => 'របាយការណ៍គ្រូ',
            'attendance' => 'របាយការណ៍វត្តមាន/អវត្តមាន',
            'scores' => 'របាយការណ៍ពិន្ទុ',
            'payments' => 'របាយការណ៍ការបង់ប្រាក់',
            'income' => 'របាយការណ៍ចំណូលប្រចាំខែ',
        ];

        return match ($role) {
            'teacher' => array_intersect_key($reports, array_flip(['students', 'attendance', 'scores'])),
            'accountant' => array_intersect_key($reports, array_flip(['payments', 'income'])),
            'student', 'student_parent' => array_intersect_key($reports, array_flip(['attendance', 'scores', 'payments'])),
            default => $reports,
        };
    }

    private function buildReport(string $type, Request $request): array
    {
        abort_unless(array_key_exists($type, $this->availableReports()), 403);

        return match ($type) {
            'students' => $this->studentsReport($request),
            'teachers' => $this->teachersReport(),
            'attendance' => $this->attendanceReport($request),
            'scores' => $this->scoresReport($request),
            'payments' => $this->paymentsReport($request),
            'income' => $this->incomeReport($request),
            default => abort(404),
        };
    }

    private function studentsReport(Request $request): array
    {
        $students = Student::with('classRoom')
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->class_id))
            ->orderBy('student_code')
            ->get();

        return [
            'title' => 'របាយការណ៍សិស្ស',
            'headers' => ['លេខសម្គាល់', 'ឈ្មោះ', 'ភេទ', 'ថ្នាក់', 'ទូរស័ព្ទ', 'អាណាព្យាបាល', 'ស្ថានភាព'],
            'rows' => $students->map(fn ($student) => [
                $student->student_code,
                $student->full_name,
                $student->gender,
                $student->classRoom?->name,
                $student->phone,
                $student->parent_name,
                $this->statusLabel($student->status),
            ])->all(),
        ];
    }

    private function teachersReport(): array
    {
        $teachers = Teacher::orderBy('teacher_code')->get();

        return [
            'title' => 'របាយការណ៍គ្រូ',
            'headers' => ['លេខសម្គាល់', 'ឈ្មោះ', 'ភេទ', 'ទូរស័ព្ទ', 'ឯកទេស', 'ថ្ងៃចូលធ្វើការ', 'ស្ថានភាព'],
            'rows' => $teachers->map(fn ($teacher) => [
                $teacher->teacher_code,
                $teacher->full_name,
                $teacher->gender,
                $teacher->phone,
                $teacher->subject_specialty,
                $teacher->hire_date?->format('Y-m-d'),
                $this->statusLabel($teacher->status),
            ])->all(),
        ];
    }

    private function attendanceReport(Request $request): array
    {
        $attendances = Attendance::with(['student.classRoom', 'subject'])
            ->when(in_array(auth()->user()->role, ['student', 'student_parent'], true), function ($query) {
                $query->whereHas('student', fn ($query) => $query->where('user_id', auth()->id()));
            })
            ->when($request->filled('from'), fn ($query) => $query->whereDate('attendance_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('attendance_date', '<=', $request->to))
            ->latest('attendance_date')
            ->get();

        return [
            'title' => 'របាយការណ៍វត្តមាន/អវត្តមាន',
            'headers' => ['កាលបរិច្ឆេទ', 'សិស្ស', 'ថ្នាក់', 'មុខវិជ្ជា', 'ស្ថានភាព', 'ចំណាំ'],
            'rows' => $attendances->map(fn ($attendance) => [
                $attendance->attendance_date?->format('Y-m-d'),
                $attendance->student?->full_name,
                $attendance->student?->classRoom?->name,
                $attendance->subject?->name,
                $attendance->statusLabel(),
                $attendance->note,
            ])->all(),
        ];
    }

    private function scoresReport(Request $request): array
    {
        $scores = Score::with(['student.classRoom', 'exam.subject'])
            ->when(in_array(auth()->user()->role, ['student', 'student_parent'], true), function ($query) {
                $query->whereHas('student', fn ($query) => $query->where('user_id', auth()->id()));
            })
            ->latest()
            ->get();

        return [
            'title' => 'របាយការណ៍ពិន្ទុ',
            'headers' => ['ការប្រឡង', 'សិស្ស', 'ថ្នាក់', 'មុខវិជ្ជា', 'ពិន្ទុ', 'និទ្ទេស'],
            'rows' => $scores->map(fn ($score) => [
                $score->exam?->title,
                $score->student?->full_name,
                $score->student?->classRoom?->name,
                $score->exam?->subject?->name,
                $score->marks,
                $score->grade,
            ])->all(),
        ];
    }

    private function paymentsReport(Request $request): array
    {
        $payments = Payment::with('student.classRoom')
            ->when(in_array(auth()->user()->role, ['student', 'student_parent'], true), function ($query) {
                $query->whereHas('student', fn ($query) => $query->where('user_id', auth()->id()));
            })
            ->when($request->filled('from'), fn ($query) => $query->whereDate('payment_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('payment_date', '<=', $request->to))
            ->latest('payment_date')
            ->get();

        return [
            'title' => 'របាយការណ៍ការបង់ប្រាក់',
            'headers' => ['ថ្ងៃបង់', 'បង្កាន់ដៃ', 'សិស្ស', 'ថ្នាក់', 'ប្រភេទ', 'ចំនួនទឹកប្រាក់', 'ស្ថានភាព'],
            'rows' => $payments->map(fn ($payment) => [
                $payment->payment_date?->format('Y-m-d'),
                $payment->receipt_no,
                $payment->student?->full_name,
                $payment->student?->classRoom?->name,
                PaymentController::feeTypes()[$payment->fee_type] ?? $payment->fee_type,
                number_format((float) $payment->amount, 2),
                PaymentController::statuses()[$payment->status] ?? $payment->status,
            ])->all(),
        ];
    }

    private function incomeReport(Request $request): array
    {
        $payments = Payment::query()
            ->where('status', 'paid')
            ->when($request->filled('year'), fn ($query) => $query->whereYear('payment_date', $request->year))
            ->get()
            ->groupBy(fn ($payment) => $payment->payment_date?->format('Y-m'));

        return [
            'title' => 'របាយការណ៍ចំណូលប្រចាំខែ',
            'headers' => ['ខែ', 'ចំនួនបង់ប្រាក់', 'ចំណូលសរុប'],
            'rows' => $payments->map(fn ($items, $month) => [
                $month,
                $items->count(),
                number_format((float) $items->sum('amount'), 2),
            ])->values()->all(),
        ];
    }

    private function statusLabel(?string $status): string
    {
        return [
            'active' => 'កំពុងប្រើ',
            'inactive' => 'ផ្អាក',
            'graduated' => 'បញ្ចប់ការសិក្សា',
            'transferred' => 'ផ្ទេរ',
        ][$status] ?? (string) $status;
    }
}
