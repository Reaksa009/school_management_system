<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Rules\ExistsModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $attendances = Attendance::with(['student', 'classRoom', 'subject', 'teacher'])
            ->when(in_array(auth()->user()->role, ['student', 'student_parent'], true), function ($query) {
                $query->whereHas('student', fn ($query) => $query->where('user_id', auth()->id()));
            })
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->class_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('attendance_date', $request->date))
            ->latest('attendance_date')
            ->paginate(12)
            ->withQueryString();

        return view('attendances.index', [
            'attendances' => $attendances,
            'classes' => ClassRoom::orderBy('name')->get(),
            'statuses' => self::statuses(),
        ]);
    }

    public function create()
    {
        return view('attendances.form', $this->formData(new Attendance([
            'attendance_date' => now(),
            'status' => 'present',
        ])));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Attendance::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'subject_id' => $data['subject_id'] ?? null,
                'attendance_date' => $data['attendance_date'],
            ],
            $data
        );

        return redirect()->route('attendances.index')->with('success', 'កត់ត្រាវត្តមានបានជោគជ័យ។');
    }

    public function edit(Attendance $attendance)
    {
        return view('attendances.form', $this->formData($attendance));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $attendance->update($this->validated($request));

        return redirect()->route('attendances.index')->with('success', 'កែប្រែវត្តមានបានជោគជ័យ។');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()->route('attendances.index')->with('success', 'លុបវត្តមានបានជោគជ័យ។');
    }

    private function formData(Attendance $attendance): array
    {
        return [
            'attendance' => $attendance,
            'students' => Student::with('classRoom')->orderBy('first_name')->get(),
            'classes' => ClassRoom::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => Teacher::orderBy('first_name')->get(),
            'statuses' => self::statuses(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'student_id' => ['required', new ExistsModel(Student::class)],
            'class_id' => ['nullable', new ExistsModel(ClassRoom::class)],
            'subject_id' => ['nullable', new ExistsModel(Subject::class)],
            'teacher_id' => ['nullable', new ExistsModel(Teacher::class)],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(self::statuses()))],
            'note' => ['nullable'],
        ]);
    }

    public static function statuses(): array
    {
        return [
            'present' => 'មានវត្តមាន',
            'absent' => 'អវត្តមាន',
            'late' => 'មកយឺត',
            'excused' => 'សុំច្បាប់',
        ];
    }
}
