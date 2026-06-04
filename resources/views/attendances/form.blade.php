@extends('layouts.app')

@section('title', $attendance->exists ? 'កែប្រែវត្តមាន' : 'កត់ត្រាវត្តមាន')
@section('subtitle', 'ជ្រើសសិស្ស ថ្ងៃ និងស្ថានភាពវត្តមាន')

@section('content')
    <form class="content-band" method="POST" action="{{ $attendance->exists ? route('attendances.update', $attendance) : route('attendances.store') }}">
        @csrf
        @if ($attendance->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field">
                <label>សិស្ស</label>
                <select name="student_id" required>
                    <option value="">ជ្រើសសិស្ស</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected(old('student_id', $attendance->student_id) == $student->id)>{{ $student->student_code }} · {{ $student->full_name }} · {{ $student->classRoom?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>ថ្នាក់</label>
                <select name="class_id">
                    <option value="">មិនទាន់កំណត់</option>
                    @foreach ($classes as $classRoom)
                        <option value="{{ $classRoom->id }}" @selected(old('class_id', $attendance->class_id) == $classRoom->id)>{{ $classRoom->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>មុខវិជ្ជា</label>
                <select name="subject_id">
                    <option value="">មិនទាន់កំណត់</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id', $attendance->subject_id) == $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>គ្រូ</label>
                <select name="teacher_id">
                    <option value="">មិនទាន់កំណត់</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(old('teacher_id', $attendance->teacher_id) == $teacher->id)>{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>កាលបរិច្ឆេទ</label>
                <input type="date" name="attendance_date" value="{{ old('attendance_date', $attendance->attendance_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="field">
                <label>ស្ថានភាព</label>
                <select name="status" required>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $attendance->status ?: 'present') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field full">
                <label>ចំណាំ</label>
                <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
            </div>
        </div>

        <div class="actions-row" style="margin-top:18px;">
            <button class="btn" type="submit"><i data-lucide="save"></i> រក្សាទុក</button>
            <a class="btn secondary" href="{{ route('attendances.index') }}">ត្រឡប់ក្រោយ</a>
        </div>
    </form>
@endsection
