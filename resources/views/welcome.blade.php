<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('school.name_en') }}</title>
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
        <h1>សូមស្វាគមន៍</h1>
        <p>ប្រព័ន្ធនេះជួយឱ្យការងាររដ្ឋបាលសាលារៀនមានល្បឿន ត្រឹមត្រូវ និងងាយស្រួលតាមដាន។</p>
        <a class="btn" href="{{ route('login') }}">ចូលប្រើប្រព័ន្ធ</a>
    </main>
</body>
</html>
