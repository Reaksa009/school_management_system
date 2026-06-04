@extends('layouts.app')

@section('title', 'ការបង់ថ្លៃសិក្សា')
@section('subtitle', 'កត់ត្រាប្រាក់ បង្កាន់ដៃ និងរបាយការណ៍ចំណូល')

@section('actions')
    @if (auth()->user()->hasAnyRole(['admin', 'accountant']))
        <a class="btn" href="{{ route('payments.create') }}"><i data-lucide="plus"></i> កត់ត្រាបង់ប្រាក់</a>
    @endif
    @if (auth()->user()->hasAnyRole(['admin', 'accountant', 'student_parent']))
        <a class="btn warning" href="{{ route('payments.khqr.create') }}"><i data-lucide="qr-code"></i> បង់ថ្លៃសិក្សា KHQR</a>
    @endif
@endsection

@section('content')
    <section class="table-panel">
        <form class="toolbar" method="GET">
            <div class="filters">
                <div class="field">
                    <label>ស្វែងរក</label>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="បង្កាន់ដៃ ឬឈ្មោះសិស្ស">
                </div>
                <div class="field">
                    <label>ស្ថានភាព</label>
                    <select name="status">
                        <option value="">ទាំងអស់</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>វិធីបង់</label>
                    <select name="method">
                        <option value="">ទាំងអស់</option>
                        @foreach ($methods as $value => $label)
                            <option value="{{ $value }}" @selected(request('method') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="btn secondary" type="submit"><i data-lucide="search"></i> ស្វែងរក</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>បង្កាន់ដៃ</th>
                    <th>ថ្ងៃបង់</th>
                    <th>សិស្ស</th>
                    <th>ថ្នាក់</th>
                    <th>ប្រភេទ</th>
                    <th>វិធីបង់</th>
                    <th>ទឹកប្រាក់</th>
                    <th>ស្ថានភាព</th>
                    <th>សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    @php
                        $isExpiredKhqr = $payment->isKhqr()
                            && $payment->status === 'pending'
                            && $payment->khqr_expires_at
                            && now()->greaterThan($payment->khqr_expires_at);
                        $displayStatus = $isExpiredKhqr ? 'expired' : $payment->status;
                    @endphp
                    <tr>
                        <td><strong>{{ $payment->receipt_no }}</strong></td>
                        <td>{{ $payment->payment_date?->format('Y-m-d') }}</td>
                        <td>{{ $payment->student?->full_name }}</td>
                        <td>{{ $payment->student?->classRoom?->name ?? '-' }}</td>
                        <td>{{ \App\Http\Controllers\PaymentController::feeTypes()[$payment->fee_type] ?? $payment->fee_type }}</td>
                        <td>{{ $methods[$payment->method] ?? $payment->method }}</td>
                        <td><strong>${{ number_format((float) $payment->amount, 2) }}</strong></td>
                        <td>
                            <span class="badge {{ $displayStatus === 'paid' ? 'success' : ($displayStatus === 'pending' ? 'warning' : 'danger') }}">
                                {{ $statuses[$displayStatus] ?? $displayStatus }}
                            </span>
                        </td>
                        <td>
                            <div class="actions-row">
                                <a class="icon-btn" href="{{ route('payments.show', $payment) }}" title="បង្កាន់ដៃ"><i data-lucide="receipt"></i></a>
                                @if ($payment->isKhqr() && $payment->status === 'pending')
                                    <form method="POST" action="{{ route('payments.khqr.check', $payment) }}">
                                        @csrf
                                        <button class="icon-btn" type="submit" title="ពិនិត្យ Bakong"><i data-lucide="refresh-cw"></i></button>
                                    </form>
                                @endif
                                @if (auth()->user()->hasAnyRole(['admin', 'accountant']))
                                    @if ($payment->status !== 'paid')
                                        <form method="POST" action="{{ route('payments.confirm', $payment) }}" onsubmit="return confirm('បញ្ជាក់ថាបានទទួលប្រាក់ផ្ទាល់មែនទេ?')">
                                            @csrf
                                            <button class="icon-btn" type="submit" title="បញ្ជាក់ប្រាក់ផ្ទាល់"><i data-lucide="badge-check"></i></button>
                                        </form>
                                    @endif
                                    <a class="icon-btn" href="{{ route('payments.edit', $payment) }}" title="កែប្រែ"><i data-lucide="pencil"></i></a>
                                    <form method="POST" action="{{ route('payments.destroy', $payment) }}" onsubmit="return confirm('តើអ្នកចង់លុបការបង់ប្រាក់នេះមែនទេ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="icon-btn danger" type="submit" title="លុប"><i data-lucide="trash-2"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty">មិនទាន់មានទិន្នន័យការបង់ប្រាក់</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $payments->links() }}</div>
    </section>
@endsection
