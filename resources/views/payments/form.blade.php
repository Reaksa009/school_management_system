@extends('layouts.app')

@section('title', $payment->exists ? 'កែប្រែការបង់ប្រាក់' : 'កត់ត្រាការបង់ប្រាក់')
@section('subtitle', 'ព័ត៌មានបង្កាន់ដៃ និងចំណូលសាលា')

@section('content')
    <form class="content-band" method="POST" action="{{ $payment->exists ? route('payments.update', $payment) : route('payments.store') }}">
        @csrf
        @if ($payment->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field">
                <label>សិស្ស</label>
                <select name="student_id" required>
                    <option value="">ជ្រើសសិស្ស</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected(old('student_id', $payment->student_id) == $student->id)>{{ $student->student_code }} · {{ $student->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>លេខបង្កាន់ដៃ</label>
                <input name="receipt_no" value="{{ old('receipt_no', $payment->receipt_no ?: 'REC-'.now()->format('Ymd-His')) }}" required>
            </div>
            <div class="field">
                <label>ថ្ងៃបង់</label>
                <input type="date" name="payment_date" value="{{ old('payment_date', $payment->payment_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="field">
                <label>ទឹកប្រាក់</label>
                <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $payment->amount) }}" required>
            </div>
            <div class="field">
                <label>វិធីបង់</label>
                <select name="method" required>
                    @foreach ($methods as $value => $label)
                        <option value="{{ $value }}" @selected(old('method', $payment->method ?: 'cash') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>ប្រភេទថ្លៃ</label>
                <select name="fee_type" required>
                    @foreach ($feeTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('fee_type', $payment->fee_type ?: 'tuition') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>ខែបង់</label>
                <input name="billing_month" value="{{ old('billing_month', $payment->billing_month) }}" placeholder="2026-06">
            </div>
            <div class="field">
                <label>ស្ថានភាព</label>
                <select name="status" required>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $payment->status ?: 'paid') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field full">
                <label>ចំណាំ</label>
                <textarea name="note">{{ old('note', $payment->note) }}</textarea>
            </div>
        </div>

        <div class="actions-row" style="margin-top:18px;">
            <button class="btn" type="submit"><i data-lucide="save"></i> រក្សាទុក</button>
            <a class="btn secondary" href="{{ route('payments.index') }}">ត្រឡប់ក្រោយ</a>
        </div>
    </form>
@endsection
