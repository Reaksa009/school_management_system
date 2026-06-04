@extends('layouts.app')

@section('title', $exam->exists ? 'កែប្រែការប្រឡង' : 'បង្កើតការប្រឡង')
@section('subtitle', 'កំណត់ថ្នាក់ មុខវិជ្ជា និងពិន្ទុសរុប')

@section('content')
    <form class="content-band" method="POST" action="{{ $exam->exists ? route('exams.update', $exam) : route('exams.store') }}">
        @csrf
        @if ($exam->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field">
                <label>ចំណងជើង</label>
                <input name="title" value="{{ old('title', $exam->title) }}" required>
            </div>
            <div class="field">
                <label>វគ្គ/ឆមាស</label>
                <input name="term" value="{{ old('term', $exam->term) }}" placeholder="ឆមាសទី ១">
            </div>
            <div class="field">
                <label>ថ្នាក់</label>
                <select name="class_id">
                    <option value="">មិនទាន់កំណត់</option>
                    @foreach ($classes as $classRoom)
                        <option value="{{ $classRoom->id }}" @selected(old('class_id', $exam->class_id) == $classRoom->id)>{{ $classRoom->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>មុខវិជ្ជា</label>
                <select name="subject_id">
                    <option value="">មិនទាន់កំណត់</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id', $exam->subject_id) == $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>ថ្ងៃប្រឡង</label>
                <input type="date" name="exam_date" value="{{ old('exam_date', $exam->exam_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
            </div>
            <div class="field">
                <label>ពិន្ទុសរុប</label>
                <input type="number" step="0.01" min="1" name="total_marks" value="{{ old('total_marks', $exam->total_marks ?: 100) }}" required>
            </div>
        </div>

        <div class="actions-row" style="margin-top:18px;">
            <button class="btn" type="submit"><i data-lucide="save"></i> រក្សាទុក</button>
            <a class="btn secondary" href="{{ route('exams.index') }}">ត្រឡប់ក្រោយ</a>
        </div>
    </form>
@endsection
