@extends('layouts.app')

@section('title', 'ព័ត៌មានសិស្ស')
@section('subtitle', $student->full_name)

@section('actions')
    @if (auth()->user()->role === 'admin')
        <a class="btn" href="{{ route('students.edit', $student) }}"><i data-lucide="pencil"></i> កែប្រែ</a>
    @endif
@endsection

@section('content')
    <section class="content-band">
        <div class="grid grid-3">
            <p><span class="muted">លេខសម្គាល់</span><br><strong>{{ $student->student_code }}</strong></p>
            <p><span class="muted">ថ្នាក់</span><br><strong>{{ $student->classRoom?->name ?? '-' }}</strong></p>
            <p><span class="muted">អាណាព្យាបាល</span><br><strong>{{ $student->parent_name ?? '-' }}</strong></p>
            <p><span class="muted">ទូរស័ព្ទ</span><br><strong>{{ $student->phone ?? '-' }}</strong></p>
            <p><span class="muted">ថ្ងៃចុះឈ្មោះ</span><br><strong>{{ $student->enrollment_date?->format('Y-m-d') ?? '-' }}</strong></p>
            <p><span class="muted">ស្ថានភាព</span><br><strong>{{ $student->status }}</strong></p>
        </div>
    </section>

    <div class="grid grid-3" style="margin-top:16px;">
        <section class="content-band">
            <h3>វត្តមានចុងក្រោយ</h3>
            @forelse ($student->attendances->sortByDesc('attendance_date')->take(8) as $attendance)
                <p>{{ $attendance->attendance_date?->format('Y-m-d') }} · {{ $attendance->subject?->name }} · <strong>{{ $attendance->statusLabel() }}</strong></p>
            @empty
                <p class="muted">មិនទាន់មានទិន្នន័យ</p>
            @endforelse
        </section>
        <section class="content-band">
            <h3>ពិន្ទុចុងក្រោយ</h3>
            @forelse ($student->scores->sortByDesc('created_at')->take(8) as $score)
                <p>{{ $score->exam?->title }} · {{ $score->exam?->subject?->name }} · <strong>{{ $score->marks }} ({{ $score->grade }})</strong></p>
            @empty
                <p class="muted">មិនទាន់មានទិន្នន័យ</p>
            @endforelse
        </section>
        <section class="content-band">
            <h3>ការបង់ប្រាក់ចុងក្រោយ</h3>
            @forelse ($student->payments->sortByDesc('payment_date')->take(8) as $payment)
                <p>{{ $payment->payment_date?->format('Y-m-d') }} · {{ $payment->receipt_no }} · <strong>${{ number_format((float) $payment->amount, 2) }}</strong></p>
            @empty
                <p class="muted">មិនទាន់មានទិន្នន័យ</p>
            @endforelse
        </section>
    </div>
@endsection
