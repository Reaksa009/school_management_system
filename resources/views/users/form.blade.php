@extends('layouts.app')

@section('title', $user->exists ? 'កែប្រែអ្នកប្រើប្រាស់' : 'បន្ថែមអ្នកប្រើប្រាស់')
@section('subtitle', 'កំណត់ព័ត៌មានគណនី ពាក្យសម្ងាត់ និងតួនាទី')

@section('content')
    <form class="content-band" method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
        @csrf
        @if ($user->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field">
                <label>ឈ្មោះ</label>
                <input name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="field">
                <label>អ៊ីមែល</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="field">
                <label>ទូរស័ព្ទ</label>
                <input name="phone" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="field">
                <label>តួនាទី</label>
                <select name="role" required>
                    @foreach (\App\Http\Controllers\UserController::roles() as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->role ?: 'student_parent') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>ពាក្យសម្ងាត់ {{ $user->exists ? '(ទុកទទេ បើមិនប្តូរ)' : '' }}</label>
                <input type="password" name="password" {{ $user->exists ? '' : 'required' }}>
            </div>
            <div class="field">
                <label>ស្ថានភាព</label>
                <input type="hidden" name="is_active" value="0">
                <label style="display:flex; align-items:center; gap:8px; font-weight:400;">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->exists ? $user->is_active : true)) style="width:auto; min-height:auto;">
                    អនុញ្ញាតឱ្យចូលប្រើ
                </label>
            </div>
        </div>

        <div class="actions-row" style="margin-top:18px;">
            <button class="btn" type="submit"><i data-lucide="save"></i> រក្សាទុក</button>
            <a class="btn secondary" href="{{ route('users.index') }}">ត្រឡប់ក្រោយ</a>
        </div>
    </form>
@endsection
