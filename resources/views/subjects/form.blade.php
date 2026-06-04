@extends('layouts.app')

@section('title', $subject->exists ? 'កែប្រែមុខវិជ្ជា' : 'បន្ថែមមុខវិជ្ជា')
@section('subtitle', 'កំណត់មុខវិជ្ជាតាមថ្នាក់ និងគ្រូបង្រៀន')

@section('content')
    <form class="content-band" method="POST" action="{{ $subject->exists ? route('subjects.update', $subject) : route('subjects.store') }}">
        @csrf
        @if ($subject->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field">
                <label>លេខកូដ</label>
                <input name="code" value="{{ old('code', $subject->code) }}" required>
            </div>
            <div class="field">
                <label>ឈ្មោះមុខវិជ្ជា</label>
                <input name="name" value="{{ old('name', $subject->name) }}" required>
            </div>
            <div class="field">
                <label>ថ្នាក់</label>
                <select name="class_id">
                    <option value="">មិនទាន់កំណត់</option>
                    @foreach ($classes as $classRoom)
                        <option value="{{ $classRoom->id }}" @selected(old('class_id', $subject->class_id) == $classRoom->id)>{{ $classRoom->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>គ្រូបង្រៀន</label>
                <select name="teacher_id">
                    <option value="">មិនទាន់កំណត់</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(old('teacher_id', $subject->teacher_id) == $teacher->id)>{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>ម៉ោងសិក្សា</label>
                <input type="number" min="1" max="20" name="credit_hours" value="{{ old('credit_hours', $subject->credit_hours ?: 1) }}" required>
            </div>
            <div class="field full">
                <label>ពិពណ៌នា</label>
                <textarea name="description">{{ old('description', $subject->description) }}</textarea>
            </div>
        </div>

        <div class="actions-row" style="margin-top:18px;">
            <button class="btn" type="submit"><i data-lucide="save"></i> រក្សាទុក</button>
            <a class="btn secondary" href="{{ route('subjects.index') }}">ត្រឡប់ក្រោយ</a>
        </div>
    </form>
@endsection
