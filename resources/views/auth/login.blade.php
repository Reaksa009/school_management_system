<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ចូលប្រើប្រព័ន្ធ · {{ config('school.name_en') }}</title>
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

        <h1>ចូលប្រើប្រព័ន្ធ</h1>
        <p>បញ្ចូលអ៊ីមែល និងពាក្យសម្ងាត់របស់អ្នក។</p>

        @if (session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="grid">
            @csrf
            <div class="field">
                <label for="email">អ៊ីមែល</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="field">
                <label for="password">ពាក្យសម្ងាត់</label>
                <input id="password" type="password" name="password" required>
            </div>
            <label class="field" style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="remember" value="1" style="width:auto; min-height:auto;">
                ចងចាំខ្ញុំ
            </label>
            <button type="submit" class="btn">ចូលប្រើប្រព័ន្ធ</button>
            <a class="btn secondary" href="{{ route('register') }}">បង្កើតគណនីសិស្ស/គ្រូ</a>
        </form>
    </main>
</body>
</html>
