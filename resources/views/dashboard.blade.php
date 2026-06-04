@extends('layouts.app')

@section('title', 'ផ្ទាំងគ្រប់គ្រង')
@section('subtitle', 'សង្ខេបព័ត៌មានសំខាន់ៗសម្រាប់ការងាររដ្ឋបាលសាលារៀន')

@php
    $user = auth()->user();
    $can = fn (array $roles) => $user->hasAnyRole($roles);
@endphp

@section('content')
    <div class="dashboard">
        @if (in_array($user->role, ['student', 'student_parent'], true))
            @if ($student)
                <section class="dashboard-hero">
                    <div class="dashboard-hero-brand">
                        <img class="dashboard-hero-logo" src="{{ asset(config('school.logo')) }}" alt="{{ config('school.short_name') }} logo">
                        <div>
                            <span class="dashboard-kicker">Student portal</span>
                            <h2>{{ $student->full_name }}</h2>
                            <p>{{ $student->student_code }} · ថ្នាក់ {{ $student->classRoom?->name ?? 'មិនទាន់កំណត់' }}</p>
                        </div>
                    </div>
                    <div class="dashboard-hero-actions">
                        <a class="btn secondary" href="{{ route('attendances.index') }}"><i data-lucide="calendar-check"></i> វត្តមាន</a>
                        @if ($user->role === 'student_parent')
                            <a class="btn warning" href="{{ route('payments.khqr.create') }}"><i data-lucide="qr-code"></i> បង់ KHQR</a>
                        @else
                            <a class="btn secondary" href="{{ route('payments.index') }}"><i data-lucide="receipt"></i> បង្កាន់ដៃ</a>
                        @endif
                    </div>
                </section>

                <div class="dashboard-stat-grid three">
                    <section class="dashboard-stat-card">
                        <span class="dashboard-stat-icon teal"><i data-lucide="calendar-check"></i></span>
                        <div>
                            <span>វត្តមានថ្មីៗ</span>
                            <strong>{{ $latestAttendances->count() }}</strong>
                            <small>កំណត់ត្រាចុងក្រោយ</small>
                        </div>
                    </section>
                    <section class="dashboard-stat-card">
                        <span class="dashboard-stat-icon amber"><i data-lucide="medal"></i></span>
                        <div>
                            <span>ពិន្ទុថ្មីៗ</span>
                            <strong>{{ $latestScores->count() }}</strong>
                            <small>លទ្ធផលប្រឡង</small>
                        </div>
                    </section>
                    <section class="dashboard-stat-card">
                        <span class="dashboard-stat-icon rose"><i data-lucide="receipt"></i></span>
                        <div>
                            <span>ការបង់ប្រាក់</span>
                            <strong>{{ $latestPayments->count() }}</strong>
                            <small>បង្កាន់ដៃថ្មីៗ</small>
                        </div>
                    </section>
                </div>

                <div class="dashboard-panels three">
                    <section class="dashboard-panel">
                        <div class="dashboard-panel-head">
                            <div>
                                <h3>វត្តមានថ្មីៗ</h3>
                                <p>តាមដានស្ថានភាពចូលរៀន</p>
                            </div>
                            <i data-lucide="calendar-days"></i>
                        </div>
                        <div class="dashboard-list">
                            @forelse ($latestAttendances as $attendance)
                                <div class="dashboard-list-item">
                                    <span class="dashboard-dot {{ $attendance->status }}"></span>
                                    <div>
                                        <strong>{{ $attendance->subject?->name ?? 'មុខវិជ្ជា' }}</strong>
                                        <span>{{ $attendance->attendance_date?->format('Y-m-d') }} · {{ $attendance->statusLabel() }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="dashboard-empty">មិនទាន់មានកំណត់ត្រា</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="dashboard-panel">
                        <div class="dashboard-panel-head">
                            <div>
                                <h3>ពិន្ទុថ្មីៗ</h3>
                                <p>លទ្ធផលប្រឡងចុងក្រោយ</p>
                            </div>
                            <i data-lucide="clipboard-list"></i>
                        </div>
                        <div class="dashboard-list">
                            @forelse ($latestScores as $score)
                                <div class="dashboard-list-item">
                                    <span class="dashboard-dot score"></span>
                                    <div>
                                        <strong>{{ $score->exam?->title ?? 'ការប្រឡង' }}</strong>
                                        <span>{{ $score->exam?->subject?->name ?? '-' }} · {{ $score->marks }} ពិន្ទុ · {{ $score->grade }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="dashboard-empty">មិនទាន់មានកំណត់ត្រា</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="dashboard-panel">
                        <div class="dashboard-panel-head">
                            <div>
                                <h3>ការបង់ប្រាក់ថ្មីៗ</h3>
                                <p>ស្ថានភាពបង្កាន់ដៃ</p>
                            </div>
                            <i data-lucide="wallet-cards"></i>
                        </div>
                        <div class="dashboard-list">
                            @forelse ($latestPayments as $payment)
                                <a class="dashboard-list-item" href="{{ route('payments.show', $payment) }}">
                                    <span class="dashboard-dot payment"></span>
                                    <div>
                                        <strong>{{ $payment->receipt_no }}</strong>
                                        <span>{{ $payment->payment_date?->format('Y-m-d') }} · USD {{ number_format((float) $payment->amount, 2) }}</span>
                                    </div>
                                </a>
                            @empty
                                <p class="dashboard-empty">មិនទាន់មានកំណត់ត្រា</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            @else
                <section class="dashboard-hero">
                    <div class="dashboard-hero-brand">
                        <img class="dashboard-hero-logo" src="{{ asset(config('school.logo')) }}" alt="{{ config('school.short_name') }} logo">
                        <div>
                            <span class="dashboard-kicker">Student portal</span>
                            <h2>មិនទាន់ភ្ជាប់គណនីទៅសិស្ស</h2>
                            <p>សូមទាក់ទងអ្នកគ្រប់គ្រង ដើម្បីភ្ជាប់គណនីនេះទៅព័ត៌មានសិស្ស។</p>
                        </div>
                    </div>
                </section>
            @endif
        @else
            @php
                $attendanceTotal = max(1, (int) $attendanceToday->sum());
                $attendanceRows = [
                    ['key' => 'present', 'label' => 'មានវត្តមាន', 'class' => 'success', 'count' => (int) ($attendanceToday['present'] ?? 0)],
                    ['key' => 'absent', 'label' => 'អវត្តមាន', 'class' => 'danger', 'count' => (int) ($attendanceToday['absent'] ?? 0)],
                    ['key' => 'late', 'label' => 'មកយឺត', 'class' => 'warning', 'count' => (int) ($attendanceToday['late'] ?? 0)],
                    ['key' => 'excused', 'label' => 'សុំច្បាប់', 'class' => 'info', 'count' => (int) ($attendanceToday['excused'] ?? 0)],
                ];
            @endphp

            <section class="dashboard-hero">
                <div class="dashboard-hero-brand">
                    <img class="dashboard-hero-logo" src="{{ asset(config('school.logo')) }}" alt="{{ config('school.short_name') }} logo">
                    <div>
                        <span class="dashboard-kicker">School overview</span>
                        <h2>{{ config('school.name_km') }}</h2>
                        <p>{{ config('school.name_en') }} · {{ now()->format('Y-m-d') }}</p>
                    </div>
                </div>
                <div class="dashboard-hero-actions">
                    @if ($can(['admin']))
                        <a class="btn secondary" href="{{ route('students.create') }}"><i data-lucide="user-plus"></i> សិស្សថ្មី</a>
                    @endif
                    @if ($can(['admin', 'teacher']))
                        <a class="btn secondary" href="{{ route('attendances.create') }}"><i data-lucide="calendar-check"></i> កត់វត្តមាន</a>
                    @endif
                    @if ($can(['admin', 'accountant']))
                        <a class="btn warning" href="{{ route('payments.khqr.create') }}"><i data-lucide="qr-code"></i> KHQR</a>
                    @endif
                </div>
            </section>

            <div class="dashboard-stat-grid">
                <section class="dashboard-stat-card">
                    <span class="dashboard-stat-icon teal"><i data-lucide="graduation-cap"></i></span>
                    <div>
                        <span>សិស្សសរុប</span>
                        <strong>{{ $stats['students'] }}</strong>
                        <small>ចំនួនសិស្សក្នុងប្រព័ន្ធ</small>
                    </div>
                </section>
                <section class="dashboard-stat-card">
                    <span class="dashboard-stat-icon blue"><i data-lucide="user-round-check"></i></span>
                    <div>
                        <span>គ្រូសរុប</span>
                        <strong>{{ $stats['teachers'] }}</strong>
                        <small>បុគ្គលិកបង្រៀន</small>
                    </div>
                </section>
                <section class="dashboard-stat-card">
                    <span class="dashboard-stat-icon amber"><i data-lucide="school"></i></span>
                    <div>
                        <span>ថ្នាក់</span>
                        <strong>{{ $stats['classes'] }}</strong>
                        <small>{{ $stats['subjects'] }} មុខវិជ្ជា</small>
                    </div>
                </section>
                <section class="dashboard-stat-card">
                    <span class="dashboard-stat-icon rose"><i data-lucide="wallet-cards"></i></span>
                    <div>
                        <span>ចំណូលខែនេះ</span>
                        <strong>USD {{ number_format((float) $stats['monthly_income'], 2) }}</strong>
                        <small>{{ $stats['users'] }} អ្នកប្រើប្រាស់</small>
                    </div>
                </section>
            </div>

            <div class="dashboard-layout">
                <section class="dashboard-panel attendance-panel">
                    <div class="dashboard-panel-head">
                        <div>
                            <h3>វត្តមានថ្ងៃនេះ</h3>
                            <p>សង្ខេបស្ថានភាពចូលរៀនប្រចាំថ្ងៃ</p>
                        </div>
                        <span class="dashboard-total">{{ $attendanceToday->sum() }}</span>
                    </div>
                    <div class="attendance-bars">
                        @foreach ($attendanceRows as $row)
                            <div class="attendance-row">
                                <div>
                                    <strong>{{ $row['label'] }}</strong>
                                    <span>{{ $row['count'] }} នាក់</span>
                                </div>
                                <div class="attendance-track">
                                    <span class="{{ $row['class'] }}" style="width: {{ round(($row['count'] / $attendanceTotal) * 100) }}%;"></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="dashboard-panel quick-panel">
                    <div class="dashboard-panel-head">
                        <div>
                            <h3>សកម្មភាពរហ័ស</h3>
                            <p>ចូលទៅមុខងារដែលប្រើញឹកញាប់</p>
                        </div>
                        <i data-lucide="sparkles"></i>
                    </div>
                    <div class="quick-actions-grid">
                        @if ($can(['admin']))
                            <a href="{{ route('students.create') }}"><i data-lucide="user-plus"></i><span>បន្ថែមសិស្ស</span></a>
                            <a href="{{ route('classes.create') }}"><i data-lucide="school"></i><span>បង្កើតថ្នាក់</span></a>
                        @endif
                        @if ($can(['admin', 'teacher']))
                            <a href="{{ route('attendances.create') }}"><i data-lucide="calendar-check"></i><span>កត់វត្តមាន</span></a>
                            <a href="{{ route('exams.create') }}"><i data-lucide="clipboard-plus"></i><span>បង្កើតប្រឡង</span></a>
                        @endif
                        @if ($can(['admin', 'accountant']))
                            <a href="{{ route('payments.create') }}"><i data-lucide="receipt"></i><span>កត់ប្រាក់</span></a>
                            <a href="{{ route('payments.khqr.create') }}"><i data-lucide="qr-code"></i><span>បង់ KHQR</span></a>
                        @endif
                        <a href="{{ route('reports.index') }}"><i data-lucide="file-bar-chart"></i><span>របាយការណ៍</span></a>
                    </div>
                </section>
            </div>

            <div class="dashboard-panels three">
                <section class="dashboard-panel">
                    <div class="dashboard-panel-head">
                        <div>
                            <h3>វត្តមានថ្មីៗ</h3>
                            <p>កំណត់ត្រាចុងក្រោយ</p>
                        </div>
                        @if ($can(['admin', 'teacher']))
                            <a href="{{ route('attendances.index') }}">មើលទាំងអស់</a>
                        @endif
                    </div>
                    <div class="dashboard-list">
                        @forelse ($latestAttendances as $attendance)
                            <div class="dashboard-list-item">
                                <span class="dashboard-dot {{ $attendance->status }}"></span>
                                <div>
                                    <strong>{{ $attendance->student?->full_name ?? '-' }}</strong>
                                    <span>{{ $attendance->attendance_date?->format('Y-m-d') }} · {{ $attendance->statusLabel() }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="dashboard-empty">មិនទាន់មានកំណត់ត្រា</p>
                        @endforelse
                    </div>
                </section>

                <section class="dashboard-panel">
                    <div class="dashboard-panel-head">
                        <div>
                            <h3>ពិន្ទុថ្មីៗ</h3>
                            <p>លទ្ធផលដែលទើបបញ្ចូល</p>
                        </div>
                        @if ($can(['admin', 'teacher']))
                            <a href="{{ route('scores.index') }}">មើលទាំងអស់</a>
                        @endif
                    </div>
                    <div class="dashboard-list">
                        @forelse ($latestScores as $score)
                            <div class="dashboard-list-item">
                                <span class="dashboard-dot score"></span>
                                <div>
                                    <strong>{{ $score->student?->full_name ?? '-' }}</strong>
                                    <span>{{ $score->exam?->title ?? 'ការប្រឡង' }} · {{ $score->marks }} ពិន្ទុ</span>
                                </div>
                            </div>
                        @empty
                            <p class="dashboard-empty">មិនទាន់មានកំណត់ត្រា</p>
                        @endforelse
                    </div>
                </section>

                <section class="dashboard-panel">
                    <div class="dashboard-panel-head">
                        <div>
                            <h3>ការបង់ប្រាក់ថ្មីៗ</h3>
                            <p>បង្កាន់ដៃ និងចំណូល</p>
                        </div>
                        @if ($can(['admin', 'accountant']))
                            <a href="{{ route('payments.index') }}">មើលទាំងអស់</a>
                        @endif
                    </div>
                    <div class="dashboard-list">
                        @forelse ($latestPayments as $payment)
                            @if ($can(['admin', 'accountant']))
                                <a class="dashboard-list-item" href="{{ route('payments.show', $payment) }}">
                                    <span class="dashboard-dot payment"></span>
                                    <div>
                                        <strong>{{ $payment->student?->full_name ?? '-' }}</strong>
                                        <span>{{ $payment->receipt_no }} · USD {{ number_format((float) $payment->amount, 2) }}</span>
                                    </div>
                                </a>
                            @else
                                <div class="dashboard-list-item">
                                    <span class="dashboard-dot payment"></span>
                                    <div>
                                        <strong>{{ $payment->student?->full_name ?? '-' }}</strong>
                                        <span>{{ $payment->receipt_no }} · USD {{ number_format((float) $payment->amount, 2) }}</span>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <p class="dashboard-empty">មិនទាន់មានកំណត់ត្រា</p>
                        @endforelse
                    </div>
                </section>
            </div>
        @endif
    </div>
@endsection
