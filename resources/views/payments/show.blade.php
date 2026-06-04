@extends('layouts.app')

@section('title', 'បង្កាន់ដៃបង់ប្រាក់')
@section('subtitle', $payment->receipt_no)

@php
    $khqrExpired = $payment->isKhqr()
        && $payment->status === 'pending'
        && $payment->khqr_expires_at
        && now()->greaterThan($payment->khqr_expires_at);
    $khqrActive = $payment->isKhqr()
        && $payment->khqr_payload
        && $payment->status === 'pending'
        && ! $khqrExpired;
    $statusLabel = \App\Http\Controllers\PaymentController::statuses()[$payment->status] ?? $payment->status;
    $statusClass = match ($payment->status) {
        'paid' => 'success',
        'expired' => 'danger',
        default => 'warning',
    };
    $statusIcon = match ($payment->status) {
        'paid' => 'check-circle-2',
        'expired' => 'timer-off',
        default => 'clock-3',
    };
    $currency = config('khqr.currency', 'USD');
    $feeTypeLabel = \App\Http\Controllers\PaymentController::feeTypes()[$payment->fee_type] ?? $payment->fee_type;
    $methodLabel = \App\Http\Controllers\PaymentController::methods()[$payment->method] ?? $payment->method;
    $showPaymentSuccessPopup = $payment->status === 'paid'
        && (session('success') || request()->boolean('payment_success'));
@endphp

@section('actions')
    <button class="btn secondary" onclick="window.print()"><i data-lucide="printer"></i> បោះពុម្ព/PDF</button>
    @if ($khqrActive)
        <button class="btn warning" type="button" id="open-khqr-modal"><i data-lucide="qr-code"></i> បង្ហាញ KHQR</button>
        <form method="POST" action="{{ route('payments.khqr.check', $payment) }}">
            @csrf
            <button class="btn warning" type="submit"><i data-lucide="refresh-cw"></i> ពិនិត្យ Bakong</button>
        </form>
    @endif
    @if (auth()->user()->hasAnyRole(['admin', 'accountant']) && $payment->status !== 'paid')
        <form method="POST" action="{{ route('payments.confirm', $payment) }}" onsubmit="return confirm('បញ្ជាក់ថាបានទទួលប្រាក់ផ្ទាល់មែនទេ?')">
            @csrf
            <button class="btn" type="submit"><i data-lucide="badge-check"></i> បញ្ជាក់ប្រាក់ផ្ទាល់</button>
        </form>
    @endif
@endsection

@section('content')
    @if ($khqrActive)
        <div id="khqr-modal" class="khqr-modal" aria-hidden="true">
            <div class="khqr-modal-panel" role="dialog" aria-modal="true" aria-labelledby="khqr-modal-title">
                <div class="khqr-modal-top">
                    <div>
                        <span class="khqr-logo">KHQR</span>
                        <h2 id="khqr-modal-title">ស្កេនបង់ថ្លៃសិក្សា</h2>
                        <p class="muted">{{ data_get($payment->meta, 'khqr.account_name') }} · {{ data_get($payment->meta, 'khqr.credential') }}</p>
                    </div>
                    <button class="icon-btn" type="button" id="close-khqr-modal" aria-label="បិទ KHQR"><i data-lucide="x"></i></button>
                </div>
                <div class="khqr-modal-body">
                    <section class="khqr-scan-panel">
                        <span class="khqr-logo">KHQR</span>
                        <div id="khqr-modal-code" class="khqr-modal-code"></div>
                        <div class="khqr-scan-amount">{{ $currency }} {{ number_format((float) $payment->amount, 2) }}</div>
                        <span class="khqr-countdown" data-khqr-countdown data-expires="{{ $payment->khqr_expires_at?->toIso8601String() }}">--:--</span>
                        <p id="khqr-poll-status" class="muted">កំពុងពិនិត្យការបង់ប្រាក់...</p>
                    </section>
                    <section class="khqr-mini-list">
                        <p><span class="muted">លេខបង្កាន់ដៃ</span><br><strong>{{ $payment->receipt_no }}</strong></p>
                        <p><span class="muted">សិស្ស</span><br><strong>{{ $payment->student?->full_name }}</strong><br>{{ $payment->student?->classRoom?->name ?? '-' }}</p>
                        <p><span class="muted">ផុតកំណត់</span><br><strong>{{ $payment->khqr_expires_at?->format('Y-m-d H:i') ?? '-' }}</strong></p>
                        <p><span class="muted">ការបញ្ជាក់</span><br>Bakong នឹងបញ្ជាក់ដោយ MD5 បន្ទាប់ពីបង់ជោគជ័យ។</p>
                        <p><span class="muted">ស្ថានភាព</span><br><strong id="khqr-modal-status">កំពុងរង់ចាំ</strong></p>
                    </section>
                </div>
            </div>
        </div>
    @endif

    <section class="receipt-sheet receipt">
        <div class="receipt-header">
            <div class="receipt-brand">
                <img class="receipt-logo" src="{{ asset(config('school.logo')) }}" alt="{{ config('school.short_name') }} logo">
                <div>
                    <span class="receipt-eyebrow">បង្កាន់ដៃបង់ប្រាក់</span>
                    <h2>{{ config('school.name_km') }}</h2>
                    <p>{{ config('school.name_en') }}</p>
                </div>
            </div>
            <span class="receipt-pill {{ $statusClass }}"><i data-lucide="{{ $statusIcon }}"></i>{{ $statusLabel }}</span>
        </div>
        <div class="receipt-body">
            <div class="receipt-total">
                <div>
                    <span>ទឹកប្រាក់សរុប</span>
                    <strong>{{ $currency }} {{ number_format((float) $payment->amount, 2) }}</strong>
                </div>
                <div class="receipt-total-meta">
                    <span class="badge {{ $statusClass }}"><i data-lucide="{{ $statusIcon }}"></i>{{ $statusLabel }}</span>
                    <small>{{ $payment->verified_at ? 'បានបញ្ជាក់រួច' : 'រង់ចាំការបញ្ជាក់' }}</small>
                </div>
            </div>
            <div class="receipt-section-title">
                <i data-lucide="receipt-text"></i>
                ព័ត៌មានបង្កាន់ដៃ
            </div>
            <div class="receipt-details">
                <div class="receipt-item"><span>លេខបង្កាន់ដៃ</span><strong class="receipt-code">{{ $payment->receipt_no }}</strong></div>
                <div class="receipt-item"><span>ថ្ងៃបង់</span><strong>{{ $payment->payment_date?->format('Y-m-d') }}</strong></div>
                <div class="receipt-item"><span>សិស្ស</span><strong>{{ $payment->student?->full_name }}</strong></div>
                <div class="receipt-item"><span>ថ្នាក់</span><strong>{{ $payment->student?->classRoom?->name ?? '-' }}</strong></div>
                <div class="receipt-item"><span>ប្រភេទថ្លៃ</span><strong>{{ $feeTypeLabel }}</strong></div>
                <div class="receipt-item"><span>វិធីបង់</span><strong>{{ $methodLabel }}</strong></div>
                <div class="receipt-item"><span>ស្ថានភាព</span><strong>{{ $statusLabel }}</strong></div>
                <div class="receipt-item"><span>អ្នកកត់ត្រា</span><strong>{{ $payment->recorder?->name ?? '-' }}</strong></div>
            @if ($payment->transaction_id)
                <div class="receipt-item full"><span>លេខប្រតិបត្តិការ</span><strong class="receipt-code">{{ $payment->transaction_id }}</strong></div>
            @endif
            @if ($payment->verified_at)
                <div class="receipt-item"><span>បានបញ្ជាក់នៅ</span><strong>{{ $payment->verified_at?->format('Y-m-d H:i') }}</strong></div>
            @endif
            </div>
            @if ($payment->note)
                <div class="receipt-note">
                    <span class="muted">ចំណាំ</span><br>{{ $payment->note }}
                </div>
            @endif
            <div class="receipt-footer">
                <span><i data-lucide="badge-check"></i> បានបង្កើតដោយ {{ $payment->recorder?->name ?? 'ប្រព័ន្ធ' }}</span>
                <span><i data-lucide="calendar-clock"></i> {{ now()->format('Y-m-d H:i') }}</span>
            </div>
        </div>
    </section>

    @if ($showPaymentSuccessPopup)
        <div id="payment-success-modal" class="payment-success-modal open" aria-hidden="false">
            <div class="payment-success-panel" role="dialog" aria-modal="true" aria-labelledby="payment-success-title">
                <button class="icon-btn payment-success-close" type="button" id="close-payment-success" aria-label="បិទផ្ទាំងជោគជ័យ">
                    <i data-lucide="x"></i>
                </button>
                <div class="payment-success-mark">
                    <i data-lucide="check"></i>
                </div>
                <p class="payment-success-kicker">ការបង់ប្រាក់ជោគជ័យ</p>
                <h2 id="payment-success-title">បានទទួលប្រាក់រួចរាល់</h2>
                <p class="muted">ប្រព័ន្ធបានបញ្ជាក់ការបង់ថ្លៃសិក្សា និងបង្កើតបង្កាន់ដៃរួចហើយ។</p>

                <div class="payment-success-amount">
                    <span>ចំនួនទឹកប្រាក់</span>
                    <strong>{{ $currency }} {{ number_format((float) $payment->amount, 2) }}</strong>
                </div>

                <div class="payment-success-details">
                    <p><span>លេខបង្កាន់ដៃ</span><strong>{{ $payment->receipt_no }}</strong></p>
                    <p><span>សិស្ស</span><strong>{{ $payment->student?->full_name }}</strong></p>
                    <p><span>វិធីបង់</span><strong>{{ \App\Http\Controllers\PaymentController::methods()[$payment->method] ?? $payment->method }}</strong></p>
                    <p><span>បានបញ្ជាក់នៅ</span><strong>{{ $payment->verified_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i') }}</strong></p>
                </div>

                <div class="payment-success-actions">
                    <button class="btn secondary" type="button" id="payment-success-done">បិទ</button>
                    <button class="btn" type="button" onclick="window.print()"><i data-lucide="printer"></i> បោះពុម្ពបង្កាន់ដៃ</button>
                </div>
            </div>
        </div>

        <script>
            window.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('payment-success-modal');
                const currentUrl = new URL(window.location.href);
                if (currentUrl.searchParams.has('payment_success')) {
                    currentUrl.searchParams.delete('payment_success');
                    window.history.replaceState({}, '', currentUrl.toString());
                }
                const closeButtons = [
                    document.getElementById('close-payment-success'),
                    document.getElementById('payment-success-done')
                ].filter(Boolean);
                const closeModal = () => {
                    if (! modal) {
                        return;
                    }
                    modal.classList.remove('open');
                    modal.setAttribute('aria-hidden', 'true');
                };

                closeButtons.forEach((button) => button.addEventListener('click', closeModal));
                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeModal();
                    }
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeModal();
                    }
                });
            });
        </script>
    @endif

    @if ($khqrActive)
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                const renderQr = (id, size) => {
                    const target = document.getElementById(id);
                    if (! target || ! window.QRCode) {
                        return;
                    }
                    target.innerHTML = '';
                    new QRCode(target, {
                        text: @json($payment->khqr_payload),
                        width: size,
                        height: size,
                        colorDark: '#111827',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M
                    });
                };
                renderQr('khqr-modal-code', 250);

                const modal = document.getElementById('khqr-modal');
                const openButtons = [
                    document.getElementById('open-khqr-modal')
                ].filter(Boolean);
                const closeButton = document.getElementById('close-khqr-modal');
                const openModal = () => {
                    if (! modal) {
                        return;
                    }
                    modal.classList.add('open');
                    modal.setAttribute('aria-hidden', 'false');
                };
                const closeModal = () => {
                    if (! modal) {
                        return;
                    }
                    modal.classList.remove('open');
                    modal.setAttribute('aria-hidden', 'true');
                };
                openButtons.forEach((button) => button.addEventListener('click', openModal));
                closeButton?.addEventListener('click', closeModal);
                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeModal();
                    }
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeModal();
                    }
                });
                openModal();

                const countdowns = document.querySelectorAll('[data-khqr-countdown]');
                const firstCountdown = countdowns[0];
                const expiresAt = firstCountdown ? new Date(firstCountdown.dataset.expires).getTime() : 0;
                const tick = () => {
                    const seconds = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
                    const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
                    const remainder = String(seconds % 60).padStart(2, '0');
                    countdowns.forEach((countdown) => {
                        countdown.textContent = `${minutes}:${remainder}`;
                    });
                    if (seconds <= 0) {
                        window.location.reload();
                    }
                };
                if (countdowns.length && expiresAt) {
                    tick();
                    setInterval(tick, 1000);
                }

                const statusText = document.getElementById('khqr-poll-status');
                const modalStatus = document.getElementById('khqr-modal-status');
                const checkPayment = async () => {
                    if (Date.now() >= expiresAt) {
                        return;
                    }

                    try {
                        const response = await fetch(@json(route('payments.khqr.check', $payment)), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();

                        if (data.status === 'paid') {
                            if (statusText) {
                                statusText.textContent = 'Bakong បានបញ្ជាក់ជោគជ័យ។';
                            }
                            if (modalStatus) {
                                modalStatus.textContent = 'បានបង់រួច';
                            }
                            const receiptUrl = data.receipt_url || window.location.href;
                            const separator = receiptUrl.includes('?') ? '&' : '?';
                            window.location.href = `${receiptUrl}${separator}payment_success=1`;
                            return;
                        }

                        if (data.status === 'expired') {
                            window.location.reload();
                            return;
                        }

                        if (statusText) {
                            statusText.textContent = 'មិនទាន់មានការបញ្ជាក់ពី Bakong។';
                        }
                        if (modalStatus) {
                            modalStatus.textContent = 'កំពុងរង់ចាំ';
                        }
                    } catch (error) {
                        if (statusText) {
                            statusText.textContent = 'កំពុងរង់ចាំការបញ្ជាក់ពី Bakong...';
                        }
                    }
                };

                checkPayment();
                setInterval(checkPayment, 5000);
            });
        </script>
    @endif
@endsection
