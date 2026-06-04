<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>បង្កើតគណនី · {{ config('school.name_en') }}</title>
    <link rel="stylesheet" href="{{ asset('css/sms.css') }}">
</head>
<body class="login-page">
    <main class="login-panel">
        <div class="brand">
            <img class="brand-logo" src="{{ asset(config('school.logo')) }}" alt="{{ config('school.short_name') }} logo">
            <span>
                <strong>{{ config('school.name_km') }}</strong>
                <span>{{ config('school.name_en') }}</span>
            </span>
        </div>

        <h1>បង្កើតគណនី</h1>
        <p>សម្រាប់សិស្ស ឬគ្រូដែលមានលេខសម្គាល់រួច។ គណនីនឹងត្រូវរង់ចាំ Admin អនុម័ត។</p>

        @if ($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="grid">
            @csrf
            <div class="field">
                <label>ប្រភេទគណនី</label>
                <select name="role" required>
                    <option value="student" @selected(old('role') === 'student')>សិស្ស</option>
                    <option value="teacher" @selected(old('role') === 'teacher')>គ្រូ</option>
                </select>
            </div>
            <div class="field">
                <label>លេខសម្គាល់សិស្ស/គ្រូ</label>
                <input name="profile_code" value="{{ old('profile_code') }}" placeholder="ឧ. S-001 ឬ T-001" required>
            </div>
            <div class="field">
                <label>ឈ្មោះគណនី</label>
                <input name="name" value="{{ old('name') }}" required>
            </div>
            <div class="field">
                <label>អ៊ីមែលចូលប្រើ</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="field">
                <label>ទូរស័ព្ទ</label>
                <input name="phone" value="{{ old('phone') }}">
            </div>
            <div class="field">
                <label>ពាក្យសម្ងាត់</label>
                <input type="password" name="password" required>
            </div>
            <div class="field">
                <label>បញ្ជាក់ពាក្យសម្ងាត់</label>
                <input type="password" name="password_confirmation" required>
            </div>
            <button type="submit" class="btn">ស្នើបង្កើតគណនី</button>
            <a class="btn secondary" href="{{ route('login') }}">ត្រឡប់ទៅ Login</a>
        </form>
    </main>
</body>
</html>
