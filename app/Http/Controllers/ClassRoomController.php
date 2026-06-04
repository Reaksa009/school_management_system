<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Teacher;
use App\Rules\ExistsModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassRoomController extends Controller
{
    public function index(Request $request)
    {
        $classes = ClassRoom::with(['teacher', 'students'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('section', 'like', "%{$search}%")
                    ->orWhere('academic_year', 'like', "%{$search}%")
                    ->orWhere('room', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        return view('classes.form', $this->formData(new ClassRoom()));
    }

    public function store(Request $request)
    {
        ClassRoom::create($this->validated($request));

        return redirect()->route('classes.index')->with('success', 'បង្កើតថ្នាក់បានជោគជ័យ។');
    }

    public function edit(ClassRoom $classRoom)
    {
        return view('classes.form', $this->formData($classRoom));
    }

    public function update(Request $request, ClassRoom $classRoom)
    {
        $classRoom->update($this->validated($request, $classRoom));

        return redirect()->route('classes.index')->with('success', 'កែប្រែថ្នាក់បានជោគជ័យ។');
    }

    public function destroy(ClassRoom $classRoom)
    {
        $classRoom->delete();

        return redirect()->route('classes.index')->with('success', 'លុបថ្នាក់បានជោគជ័យ។');
    }

    private function formData(ClassRoom $classRoom): array
    {
        return [
            'classRoom' => $classRoom,
            'teachers' => Teacher::orderBy('first_name')->get(),
        ];
    }

    private function validated(Request $request, ?ClassRoom $classRoom = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'max:100',
                Rule::unique('classes')
                    ->where(fn ($query) => $query
                        ->where('section', $request->section)
                        ->where('academic_year', $request->academic_year))
                    ->ignore($classRoom?->getKey(), '_id'),
            ],
            'section' => ['nullable', 'max:50'],
            'academic_year' => ['nullable', 'max:50'],
            'room' => ['nullable', 'max:50'],
            'teacher_id' => ['nullable', new ExistsModel(Teacher::class)],
            'description' => ['nullable'],
        ]);
    }
}
