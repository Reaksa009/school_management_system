<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('school.name_km'))</title>
    <link rel="stylesheet" href="{{ asset('css/sms.css') }}">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
</head>
<body>
@php
    $user = auth()->user();
    $can = fn (array $roles) => $user && $user->hasAnyRole($roles);
    $navItems = [
        ['route' => 'dashboard', 'label' => 'ផ្ទាំងគ្រប់គ្រង', 'icon' => 'layout-dashboard', 'roles' => ['admin', 'teacher', 'accountant', 'student', 'student_parent']],
        ['route' => 'students.index', 'label' => 'សិស្ស', 'icon' => 'graduation-cap', 'roles' => ['admin', 'teacher', 'student', 'student_parent']],
        ['route' => 'teachers.index', 'label' => 'គ្រូ', 'icon' => 'user-round-check', 'roles' => ['admin', 'teacher']],
        ['route' => 'classes.index', 'label' => 'ថ្នាក់', 'icon' => 'school', 'roles' => ['admin', 'teacher']],
        ['route' => 'subjects.index', 'label' => 'មុខវិជ្ជា', 'icon' => 'book-open', 'roles' => ['admin', 'teacher']],
        ['route' => 'attendances.index', 'label' => 'វត្តមាន', 'icon' => 'calendar-check', 'roles' => ['admin', 'teacher', 'student', 'student_parent']],
        ['route' => 'exams.index', 'label' => 'ការប្រឡង', 'icon' => 'clipboard-list', 'roles' => ['admin', 'teacher']],
        ['route' => 'scores.index', 'label' => 'ពិន្ទុ', 'icon' => 'medal', 'roles' => ['admin', 'teacher']],
        ['route' => 'payments.index', 'label' => 'បង់ប្រាក់', 'icon' => 'wallet-cards', 'roles' => ['admin', 'accountant', 'student', 'student_parent']],
        ['route' => 'reports.index', 'label' => 'របាយការណ៍', 'icon' => 'file-bar-chart', 'roles' => ['admin', 'teacher', 'accountant', 'student', 'student_parent']],
        ['route' => 'users.index', 'label' => 'អ្នកប្រើប្រាស់', 'icon' => 'users', 'roles' => ['admin']],
    ];
@endphp
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <img class="brand-logo" src="{{ asset(config('school.logo')) }}" alt="{{ config('school.short_name') }} logo">
            <span>
                <strong>{{ config('school.name_km') }}</strong>
                <span>{{ config('school.name_en') }}</span>
            </span>
        </a>

        <nav class="nav">
            @foreach ($navItems as $item)
                @if ($can($item['roles']))
                    <a href="{{ route($item['route']) }}" class="{{ request()->routeIs(Str::before($item['route'], '.').'.*') || request()->routeIs($item['route']) ? 'active' : '' }}">
                        <i data-lucide="{{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="page-title">
                <h1>@yield('title', 'ផ្ទាំងគ្រប់គ្រង')</h1>
                <p>@yield('subtitle', config('school.name_en'))</p>
            </div>
            <div class="user-menu">
                <span>{{ $user->name }} · {{ $user->roleLabel() }}</span>
                @yield('actions')
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn secondary" type="submit"><i data-lucide="log-out"></i> ចាកចេញ</button>
                </form>
            </div>
        </header>

        @if (session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="error-box">
                <strong>សូមពិនិត្យទិន្នន័យម្តងទៀត</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
</body>
</html>
