@extends('layouts.app')

@section('title', 'បង់ថ្លៃសិក្សា KHQR')
@section('subtitle', 'បង្កើត KHQR សម្រាប់ស្កេនតាម Bakong')

@section('content')
    <div class="receipt">
        <form class="content-band" method="POST" action="{{ route('payments.khqr.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field full">
                    <label>សិស្ស</label>
                    <select name="student_id" required>
                        <option value="">ជ្រើសសិស្ស</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->student_code }} · {{ $student->full_name }} · {{ $student->classRoom?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>ចំនួនទឹកប្រាក់</label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', '120.00') }}" required>
                </div>
                <div class="field">
                    <label>ខែបង់</label>
                    <input name="billing_month" value="{{ old('billing_month', now()->format('Y-m')) }}" placeholder="2026-06">
                </div>
                <div class="field full">
                    <label>ចំណាំ</label>
                    <textarea name="note">{{ old('note') }}</textarea>
                </div>
            </div>

            <div class="actions-row" style="margin-top:18px;">
                <button class="btn warning" type="submit"><i data-lucide="qr-code"></i> បង្កើត KHQR</button>
                <a class="btn secondary" href="{{ route('payments.index') }}">ត្រឡប់ក្រោយ</a>
            </div>
        </form>
    </div>
@endsection
