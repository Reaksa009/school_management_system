<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use App\Rules\ExistsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['classRoom', 'user'])
            ->when(in_array(auth()->user()->role, ['student', 'student_parent'], true), fn ($query) => $query->where('user_id', auth()->id()))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($query) use ($search) {
                    $query->where('student_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('parent_name', 'like', "%{$search}%")
                        ->orWhere('parent_phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->class_id));

        $students = $query->latest()->paginate(10)->withQueryString();
        $classes = ClassRoom::orderBy('name')->get();

        return view('students.index', compact('students', 'classes'));
    }

    public function create()
    {
        return view('students.form', $this->formData(new Student()));
    }

    public function importForm()
    {
        return view('students.import');
    }

    public function store(Request $request)
    {
        Student::create($this->validated($request));

        return redirect()->route('students.index')->with('success', 'បង្កើតព័ត៌មានសិស្សបានជោគជ័យ។');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:4096'],
        ]);

        [$rows, $fileErrors] = $this->csvRows($request->file('csv_file')->getRealPath());

        if ($fileErrors) {
            return back()->withErrors(['csv_file' => implode(' ', $fileErrors)]);
        }

        $created = 0;
        $updated = 0;
        $rowErrors = [];

        DB::transaction(function () use ($rows, &$created, &$updated, &$rowErrors) {
            foreach ($rows as $rowNumber => $row) {
                $data = $this->studentImportData($row);

                $validator = Validator::make($data, [
                    'student_code' => ['required', 'max:50'],
                    'first_name' => ['required', 'max:255'],
                    'last_name' => ['required', 'max:255'],
                    'class_id' => ['nullable', new ExistsModel(ClassRoom::class)],
                    'class_name' => ['nullable', 'max:255'],
                    'parent_email' => ['nullable', 'email', 'max:255'],
                    'gender' => ['nullable', 'max:20'],
                    'date_of_birth' => ['nullable', 'date'],
                    'phone' => ['nullable', 'max:50'],
                    'email' => ['nullable', 'email', 'max:255'],
                    'address' => ['nullable'],
                    'parent_name' => ['nullable', 'max:255'],
                    'parent_phone' => ['nullable', 'max:50'],
                    'enrollment_date' => ['nullable', 'date'],
                    'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'transferred'])],
                ]);

                if ($validator->fails()) {
                    $rowErrors[] = 'ជួរ '.$rowNumber.': '.$validator->errors()->first();
                    continue;
                }

                $classId = $this->resolveClassId($data);
                if ($classId === false) {
                    $rowErrors[] = 'ជួរ '.$rowNumber.': មិនឃើញថ្នាក់ "'.$data['class_name'].'".';
                    continue;
                }

                $userId = null;
                if ($data['parent_email']) {
                    $userId = User::where('role', 'student_parent')->where('email', $data['parent_email'])->value('id');
                    if (! $userId) {
                        $rowErrors[] = 'ជួរ '.$rowNumber.': មិនឃើញគណនីអាណាព្យាបាល "'.$data['parent_email'].'".';
                        continue;
                    }
                }

                $student = Student::where('student_code', $data['student_code'])->first();
                $payload = [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                ];

                foreach (['gender', 'date_of_birth', 'phone', 'email', 'address', 'parent_name', 'parent_phone', 'enrollment_date'] as $field) {
                    if (! $student || array_key_exists($field, $row)) {
                        $payload[$field] = $data[$field];
                    }
                }

                if (! $student || array_key_exists('status', $row)) {
                    $payload['status'] = $data['status'];
                }

                if ($data['parent_email']) {
                    $payload['user_id'] = $userId;
                }

                if ($data['class_id'] || $data['class_name']) {
                    $payload['class_id'] = $classId;
                }

                if ($student) {
                    $student->update($payload);
                    $updated++;
                    continue;
                }

                Student::create($payload + ['student_code' => $data['student_code']]);
                $created++;
            }
        });

        return redirect()
            ->route('students.index')
            ->with('success', "Import សិស្សបានបញ្ចប់: បង្កើត {$created}, កែប្រែ {$updated}.")
            ->with('import_errors', $rowErrors);
    }

    public function show(Student $student)
    {
        $this->abortIfParentCannotView($student);

        $student->load(['classRoom', 'user', 'attendances.subject', 'scores.exam.subject', 'payments']);

        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        return view('students.form', $this->formData($student));
    }

    public function update(Request $request, Student $student)
    {
        $student->update($this->validated($request, $student));

        return redirect()->route('students.index')->with('success', 'កែប្រែព័ត៌មានសិស្សបានជោគជ័យ។');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'លុបព័ត៌មានសិស្សបានជោគជ័យ។');
    }

    private function formData(Student $student): array
    {
        return [
            'student' => $student,
            'classes' => ClassRoom::orderBy('name')->get(),
            'parentUsers' => User::where('role', 'student_parent')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'user_id' => ['nullable', new ExistsModel(User::class)],
            'class_id' => ['nullable', new ExistsModel(ClassRoom::class)],
            'student_code' => ['required', 'max:50', Rule::unique('students', 'student_code')->ignore($student?->getKey(), '_id')],
            'first_name' => ['required', 'max:255'],
            'last_name' => ['required', 'max:255'],
            'gender' => ['nullable', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable'],
            'parent_name' => ['nullable', 'max:255'],
            'parent_phone' => ['nullable', 'max:50'],
            'enrollment_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'transferred'])],
        ]);
    }

    private function abortIfParentCannotView(Student $student): void
    {
        if (auth()->user()->role === 'student_parent' && $student->user_id !== auth()->id()) {
            abort(403, 'អ្នកមិនមានសិទ្ធិមើលព័ត៌មានសិស្សនេះទេ។');
        }

        if (auth()->user()->role === 'student' && $student->user_id !== auth()->id()) {
            abort(403, 'អ្នកមិនមានសិទ្ធិមើលព័ត៌មានសិស្សនេះទេ។');
        }
    }

    private function csvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (! $handle) {
            return [[], ['មិនអាចអាន CSV file បានទេ។']];
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return [[], ['CSV file មិនមាន header ទេ។']];
        }

        $header = array_map(fn ($value) => $this->csvKey($value), $header);
        $rows = [];
        $rowNumber = 1;

        while (($line = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (! array_filter($line, fn ($value) => trim((string) $value) !== '')) {
                continue;
            }

            $rows[$rowNumber] = array_combine($header, array_slice(array_pad($line, count($header), null), 0, count($header)));
        }

        fclose($handle);

        return [$rows, []];
    }

    private function csvKey(?string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);

        return str_replace([' ', '-'], '_', strtolower(trim($value)));
    }

    private function studentImportData(array $row): array
    {
        $value = fn (string $key, ?string $default = null) => ($row[$key] ?? '') !== '' ? trim((string) $row[$key]) : $default;

        return [
            'student_code' => $value('student_code'),
            'first_name' => $value('first_name'),
            'last_name' => $value('last_name'),
            'class_id' => $value('class_id'),
            'class_name' => $value('class_name'),
            'parent_email' => $value('parent_email'),
            'gender' => $value('gender'),
            'date_of_birth' => $value('date_of_birth'),
            'phone' => $value('phone'),
            'email' => $value('email'),
            'address' => $value('address'),
            'parent_name' => $value('parent_name'),
            'parent_phone' => $value('parent_phone'),
            'enrollment_date' => $value('enrollment_date'),
            'status' => $value('status', 'active'),
        ];
    }

    private function resolveClassId(array $data): string|false|null
    {
        if ($data['class_id']) {
            return $data['class_id'];
        }

        if ($data['class_name']) {
            return ClassRoom::where('name', $data['class_name'])->value('id') ?: false;
        }

        return null;
    }
}
