<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Score;
use App\Models\Student;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function index()
    {
        $exams = Exam::with(['classRoom', 'subject', 'scores'])
            ->latest('exam_date')
            ->paginate(10);

        return view('scores.index', compact('exams'));
    }

    public function edit(Exam $exam)
    {
        $exam->load(['classRoom', 'subject', 'scores']);

        $students = Student::query()
            ->when($exam->class_id, fn ($query) => $query->where('class_id', $exam->class_id))
            ->orderBy('first_name')
            ->get();

        $scores = $exam->scores->keyBy('student_id');

        return view('scores.form', compact('exam', 'students', 'scores'));
    }

    public function update(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'scores' => ['nullable', 'array'],
            'scores.*.marks' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'scores.*.note' => ['nullable', 'max:1000'],
        ]);

        foreach ($data['scores'] ?? [] as $studentId => $score) {
            if (($score['marks'] ?? '') === '') {
                Score::where('exam_id', $exam->id)->where('student_id', $studentId)->delete();
                continue;
            }

            $marks = (float) $score['marks'];

            Score::updateOrCreate(
                ['exam_id' => $exam->id, 'student_id' => $studentId],
                [
                    'marks' => $marks,
                    'grade' => $this->grade($marks, (float) $exam->total_marks),
                    'note' => $score['note'] ?? null,
                ]
            );
        }

        return redirect()->route('scores.index')->with('success', 'រក្សាទុកពិន្ទុបានជោគជ័យ។');
    }

    private function grade(float $marks, float $total): string
    {
        $percent = $total > 0 ? ($marks / $total) * 100 : 0;

        return match (true) {
            $percent >= 90 => 'A',
            $percent >= 80 => 'B',
            $percent >= 70 => 'C',
            $percent >= 60 => 'D',
            $percent >= 50 => 'E',
            default => 'F',
        };
    }
}
