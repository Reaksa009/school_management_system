<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Exam;
use App\Models\Subject;
use App\Rules\ExistsModel;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::with(['classRoom', 'subject', 'scores'])
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->class_id))
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', $request->subject_id))
            ->latest('exam_date')
            ->paginate(10)
            ->withQueryString();

        return view('exams.index', [
            'exams' => $exams,
            'classes' => ClassRoom::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('exams.form', $this->formData(new Exam([
            'exam_date' => now(),
            'total_marks' => 100,
        ])));
    }

    public function store(Request $request)
    {
        Exam::create($this->validated($request));

        return redirect()->route('exams.index')->with('success', 'បង្កើតការប្រឡងបានជោគជ័យ។');
    }

    public function edit(Exam $exam)
    {
        return view('exams.form', $this->formData($exam));
    }

    public function update(Request $request, Exam $exam)
    {
        $exam->update($this->validated($request));

        return redirect()->route('exams.index')->with('success', 'កែប្រែការប្រឡងបានជោគជ័យ។');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'លុបការប្រឡងបានជោគជ័យ។');
    }

    private function formData(Exam $exam): array
    {
        return [
            'exam' => $exam,
            'classes' => ClassRoom::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'class_id' => ['nullable', new ExistsModel(ClassRoom::class)],
            'subject_id' => ['nullable', new ExistsModel(Subject::class)],
            'title' => ['required', 'max:255'],
            'term' => ['nullable', 'max:100'],
            'exam_date' => ['nullable', 'date'],
            'total_marks' => ['required', 'numeric', 'min:1'],
        ]);
    }
}
