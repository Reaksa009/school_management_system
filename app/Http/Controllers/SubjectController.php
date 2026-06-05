<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Rules\ExistsModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::with(['classRoom', 'teacher'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->class_id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('subjects.index', [
            'subjects' => $subjects,
            'classes' => ClassRoom::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('subjects.form', $this->formData(new Subject()));
    }

    public function store(Request $request)
    {
        Subject::create($this->validated($request));

        return redirect()->route('subjects.index')->with('success', 'បង្កើតមុខវិជ្ជាបានជោគជ័យ។');
    }

    public function edit(Subject $subject)
    {
        return view('subjects.form', $this->formData($subject));
    }

    public function update(Request $request, Subject $subject)
    {
        $subject->update($this->validated($request, $subject));

        return redirect()->route('subjects.index')->with('success', 'កែប្រែមុខវិជ្ជាបានជោគជ័យ។');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'លុបមុខវិជ្ជាបានជោគជ័យ។');
    }

    private function formData(Subject $subject): array
    {
        return [
            'subject' => $subject,
            'classes' => ClassRoom::orderBy('name')->get(),
            'teachers' => Teacher::orderBy('first_name')->get(),
        ];
    }

    private function validated(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'class_id' => ['nullable', new ExistsModel(ClassRoom::class)],
            'teacher_id' => ['nullable', new ExistsModel(Teacher::class)],
            'code' => ['required', 'max:50', Rule::unique('subjects', 'code')->ignore($subject?->getKey(), (new Subject())->getKeyName())],
            'name' => ['required', 'max:255'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:20'],
            'description' => ['nullable'],
        ]);
    }
}
