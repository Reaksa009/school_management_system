<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Rules\ExistsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Teacher::with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($query) use ($search) {
                    $query->where('teacher_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('subject_specialty', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('teachers.form', $this->formData(new Teacher()));
    }

    public function importForm()
    {
        return view('teachers.import');
    }

    public function store(Request $request)
    {
        Teacher::create($this->validated($request));

        return redirect()->route('teachers.index')->with('success', 'បង្កើតព័ត៌មានគ្រូបានជោគជ័យ។');
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
                $data = $this->teacherImportData($row);

                $validator = Validator::make($data, [
                    'teacher_code' => ['required', 'max:50'],
                    'first_name' => ['required', 'max:255'],
                    'last_name' => ['required', 'max:255'],
                    'user_email' => ['nullable', 'email', 'max:255'],
                    'gender' => ['nullable', 'max:20'],
                    'phone' => ['nullable', 'max:50'],
                    'email' => ['nullable', 'email', 'max:255'],
                    'address' => ['nullable'],
                    'subject_specialty' => ['nullable', 'max:255'],
                    'hire_date' => ['nullable', 'date'],
                    'status' => ['required', Rule::in(['active', 'inactive'])],
                ]);

                if ($validator->fails()) {
                    $rowErrors[] = 'ជួរ '.$rowNumber.': '.$validator->errors()->first();
                    continue;
                }

                $userId = null;
                if ($data['user_email']) {
                    $userId = User::where('role', 'teacher')->where('email', $data['user_email'])->value('id');
                    if (! $userId) {
                        $rowErrors[] = 'ជួរ '.$rowNumber.': មិនឃើញគណនីគ្រូ "'.$data['user_email'].'".';
                        continue;
                    }
                }

                $teacher = Teacher::where('teacher_code', $data['teacher_code'])->first();
                $payload = [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                ];

                foreach (['gender', 'phone', 'email', 'address', 'subject_specialty', 'hire_date'] as $field) {
                    if (! $teacher || array_key_exists($field, $row)) {
                        $payload[$field] = $data[$field];
                    }
                }

                if (! $teacher || array_key_exists('status', $row)) {
                    $payload['status'] = $data['status'];
                }

                if ($data['user_email']) {
                    $payload['user_id'] = $userId;
                }

                if ($teacher) {
                    $teacher->update($payload);
                    $updated++;
                    continue;
                }

                Teacher::create($payload + ['teacher_code' => $data['teacher_code']]);
                $created++;
            }
        });

        return redirect()
            ->route('teachers.index')
            ->with('success', "Import គ្រូបានបញ្ចប់: បង្កើត {$created}, កែប្រែ {$updated}.")
            ->with('import_errors', $rowErrors);
    }

    public function show(Teacher $teacher)
    {
        $teacher->load(['user', 'classes', 'subjects']);

        return view('teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        return view('teachers.form', $this->formData($teacher));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $teacher->update($this->validated($request, $teacher));

        return redirect()->route('teachers.index')->with('success', 'កែប្រែព័ត៌មានគ្រូបានជោគជ័យ។');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', 'លុបព័ត៌មានគ្រូបានជោគជ័យ។');
    }

    private function formData(Teacher $teacher): array
    {
        return [
            'teacher' => $teacher,
            'teacherUsers' => User::where('role', 'teacher')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?Teacher $teacher = null): array
    {
        return $request->validate([
            'user_id' => ['nullable', new ExistsModel(User::class)],
            'teacher_code' => ['required', 'max:50', Rule::unique('teachers', 'teacher_code')->ignore($teacher?->getKey(), (new Teacher())->getKeyName())],
            'first_name' => ['required', 'max:255'],
            'last_name' => ['required', 'max:255'],
            'gender' => ['nullable', 'max:20'],
            'phone' => ['nullable', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable'],
            'subject_specialty' => ['nullable', 'max:255'],
            'hire_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
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

    private function teacherImportData(array $row): array
    {
        $value = fn (string $key, ?string $default = null) => ($row[$key] ?? '') !== '' ? trim((string) $row[$key]) : $default;

        return [
            'teacher_code' => $value('teacher_code'),
            'first_name' => $value('first_name'),
            'last_name' => $value('last_name'),
            'user_email' => $value('user_email'),
            'gender' => $value('gender'),
            'phone' => $value('phone'),
            'email' => $value('email'),
            'address' => $value('address'),
            'subject_specialty' => $value('subject_specialty'),
            'hire_date' => $value('hire_date'),
            'status' => $value('status', 'active'),
        ];
    }
}
