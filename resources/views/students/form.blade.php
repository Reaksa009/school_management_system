@extends('layouts.app')

@section('title', $student->exists ? 'កែប្រែសិស្ស' : 'បន្ថែមសិស្ស')
@section('subtitle', 'ព័ត៌មានសិស្ស ថ្នាក់ និងអាណាព្យាបាល')

@section('content')
    <form class="content-band" method="POST" action="{{ $student->exists ? route('students.update', $student) : route('students.store') }}">
        @csrf
        @if ($student->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field">
                <label>លេខសម្គាល់សិស្ស</label>
                <input name="student_code" value="{{ old('student_code', $student->student_code) }}" required>
            </div>
            <div class="field">
                <label>ថ្នាក់</label>
                <select name="class_id">
                    <option value="">ជ្រើសថ្នាក់</option>
                    @foreach ($classes as $classRoom)
                        <option value="{{ $classRoom->id }}" @selected(old('class_id', $student->class_id) == $classRoom->id)>{{ $classRoom->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>នាមខ្លួន</label>
                <input name="first_name" value="{{ old('first_name', $student->first_name) }}" required>
            </div>
            <div class="field">
                <label>នាមត្រកូល</label>
                <input name="last_name" value="{{ old('last_name', $student->last_name) }}" required>
            </div>
            <div class="field">
                <label>ភេទ</label>
                <select name="gender">
                    <option value="">ជ្រើសភេទ</option>
                    @foreach (['ប្រុស', 'ស្រី'] as $gender)
                        <option value="{{ $gender }}" @selected(old('gender', $student->gender) === $gender)>{{ $gender }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>ថ្ងៃខែឆ្នាំកំណើត</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}">
            </div>
            <div class="field">
                <label>ទូរស័ព្ទសិស្ស</label>
                <input name="phone" value="{{ old('phone', $student->phone) }}">
            </div>
            <div class="field">
                <label>អ៊ីមែលសិស្ស</label>
                <input type="email" name="email" value="{{ old('email', $student->email) }}">
            </div>
            <div class="field">
                <label>ឈ្មោះអាណាព្យាបាល</label>
                <input name="parent_name" value="{{ old('parent_name', $student->parent_name) }}">
            </div>
            <div class="field">
                <label>ទូរស័ព្ទអាណាព្យាបាល</label>
                <input name="parent_phone" value="{{ old('parent_phone', $student->parent_phone) }}">
            </div>
            <div class="field">
                <label>គណនីអាណាព្យាបាល</label>
                <select name="user_id">
                    <option value="">មិនភ្ជាប់គណនី</option>
                    @foreach ($parentUsers as $parentUser)
                        <option value="{{ $parentUser->id }}" @selected(old('user_id', $student->user_id) == $parentUser->id)>{{ $parentUser->name }} · {{ $parentUser->email }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>ថ្ងៃចុះឈ្មោះ</label>
                <input type="date" name="enrollment_date" value="{{ old('enrollment_date', $student->enrollment_date?->format('Y-m-d')) }}">
            </div>
            <div class="field">
                <label>ស្ថានភាព</label>
                <select name="status" required>
                    @foreach (['active' => 'កំពុងរៀន', 'inactive' => 'ផ្អាក', 'graduated' => 'បញ្ចប់ការសិក្សា', 'transferred' => 'ផ្ទេរ'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $student->status ?: 'active') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field full">
                <label>អាសយដ្ឋាន</label>
                <textarea name="address">{{ old('address', $student->address) }}</textarea>
            </div>
        </div>

        <div class="actions-row" style="margin-top:18px;">
            <button class="btn" type="submit"><i data-lucide="save"></i> រក្សាទុក</button>
            <a class="btn secondary" href="{{ route('students.index') }}">ត្រឡប់ក្រោយ</a>
        </div>
    </form>
@endsection
