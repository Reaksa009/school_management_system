@extends('layouts.app')

@section('title', $teacher->exists ? 'កែប្រែគ្រូ' : 'បន្ថែមគ្រូ')
@section('subtitle', 'ព័ត៌មានគ្រូ និងគណនីចូលប្រើ')

@section('content')
    <form class="content-band" method="POST" action="{{ $teacher->exists ? route('teachers.update', $teacher) : route('teachers.store') }}">
        @csrf
        @if ($teacher->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field">
                <label>លេខសម្គាល់គ្រូ</label>
                <input name="teacher_code" value="{{ old('teacher_code', $teacher->teacher_code) }}" required>
            </div>
            <div class="field">
                <label>គណនីគ្រូ</label>
                <select name="user_id">
                    <option value="">មិនភ្ជាប់គណនី</option>
                    @foreach ($teacherUsers as $teacherUser)
                        <option value="{{ $teacherUser->id }}" @selected(old('user_id', $teacher->user_id) == $teacherUser->id)>{{ $teacherUser->name }} · {{ $teacherUser->email }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>នាមខ្លួន</label>
                <input name="first_name" value="{{ old('first_name', $teacher->first_name) }}" required>
            </div>
            <div class="field">
                <label>នាមត្រកូល</label>
                <input name="last_name" value="{{ old('last_name', $teacher->last_name) }}" required>
            </div>
            <div class="field">
                <label>ភេទ</label>
                <select name="gender">
                    <option value="">ជ្រើសភេទ</option>
                    @foreach (['ប្រុស', 'ស្រី'] as $gender)
                        <option value="{{ $gender }}" @selected(old('gender', $teacher->gender) === $gender)>{{ $gender }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>ទូរស័ព្ទ</label>
                <input name="phone" value="{{ old('phone', $teacher->phone) }}">
            </div>
            <div class="field">
                <label>អ៊ីមែល</label>
                <input type="email" name="email" value="{{ old('email', $teacher->email) }}">
            </div>
            <div class="field">
                <label>ឯកទេស</label>
                <input name="subject_specialty" value="{{ old('subject_specialty', $teacher->subject_specialty) }}">
            </div>
            <div class="field">
                <label>ថ្ងៃចូលធ្វើការ</label>
                <input type="date" name="hire_date" value="{{ old('hire_date', $teacher->hire_date?->format('Y-m-d')) }}">
            </div>
            <div class="field">
                <label>ស្ថានភាព</label>
                <select name="status" required>
                    <option value="active" @selected(old('status', $teacher->status ?: 'active') === 'active')>កំពុងបង្រៀន</option>
                    <option value="inactive" @selected(old('status', $teacher->status) === 'inactive')>ផ្អាក</option>
                </select>
            </div>
            <div class="field full">
                <label>អាសយដ្ឋាន</label>
                <textarea name="address">{{ old('address', $teacher->address) }}</textarea>
            </div>
        </div>

        <div class="actions-row" style="margin-top:18px;">
            <button class="btn" type="submit"><i data-lucide="save"></i> រក្សាទុក</button>
            <a class="btn secondary" href="{{ route('teachers.index') }}">ត្រឡប់ក្រោយ</a>
        </div>
    </form>
@endsection
