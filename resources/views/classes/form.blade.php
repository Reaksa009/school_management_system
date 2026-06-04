@extends('layouts.app')

@section('title', $classRoom->exists ? 'កែប្រែថ្នាក់' : 'បន្ថែមថ្នាក់')
@section('subtitle', 'ព័ត៌មានថ្នាក់ និងគ្រូទទួលខុសត្រូវ')

@section('content')
    <form class="content-band" method="POST" action="{{ $classRoom->exists ? route('classes.update', $classRoom) : route('classes.store') }}">
        @csrf
        @if ($classRoom->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field">
                <label>ឈ្មោះថ្នាក់</label>
                <input name="name" value="{{ old('name', $classRoom->name) }}" required>
            </div>
            <div class="field">
                <label>ផ្នែក</label>
                <input name="section" value="{{ old('section', $classRoom->section) }}">
            </div>
            <div class="field">
                <label>ឆ្នាំសិក្សា</label>
                <input name="academic_year" value="{{ old('academic_year', $classRoom->academic_year) }}" placeholder="2025-2026">
            </div>
            <div class="field">
                <label>បន្ទប់</label>
                <input name="room" value="{{ old('room', $classRoom->room) }}">
            </div>
            <div class="field full">
                <label>គ្រូទទួលខុសត្រូវ</label>
                <select name="teacher_id">
                    <option value="">មិនទាន់កំណត់</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(old('teacher_id', $classRoom->teacher_id) == $teacher->id)>{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field full">
                <label>ចំណាំ</label>
                <textarea name="description">{{ old('description', $classRoom->description) }}</textarea>
            </div>
        </div>

        <div class="actions-row" style="margin-top:18px;">
            <button class="btn" type="submit"><i data-lucide="save"></i> រក្សាទុក</button>
            <a class="btn secondary" href="{{ route('classes.index') }}">ត្រឡប់ក្រោយ</a>
        </div>
    </form>
@endsection
