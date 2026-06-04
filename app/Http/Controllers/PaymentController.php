<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Rules\ExistsModel;
use App\Services\KHQRTuitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['student.classRoom', 'recorder'])
            ->when(in_array(auth()->user()->role, ['student', 'student_parent'], true), function ($query) {
                $query->whereHas('student', fn ($query) => $query->where('user_id', auth()->id()));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where('receipt_no', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($query) use ($search) {
                        $query->where('student_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('method'), fn ($query) => $query->where('method', $request->method))
            ->latest('payment_date')
            ->paginate(12)
            ->withQueryString();

        return view('payments.index', [
            'payments' => $payments,
            'statuses' => self::statuses(),
            'methods' => self::methods(),
        ]);
    }

    public function create()
    {
        return view('payments.form', $this->formData(new Payment([
            'payment_date' => now(),
            'status' => 'paid',
            'method' => 'cash',
            'fee_type' => 'tuition',
        ])));
    }

    public function store(Request $request)
    {
        Payment::create($this->validated($request) + ['recorded_by' => auth()->id()]);

        return redirect()->route('payments.index')->with('success', 'កត់ត្រាការបង់ប្រាក់បានជោគជ័យ។');
    }

    public function createKhqr()
    {
        return view('payments.khqr', [
            'students' => $this->payableStudents(),
            'payment' => null,
        ]);
    }

    public function storeKhqr(Request $request, KHQRTuitionService $khqr)
    {
        $data = $request->validate([
            'student_id' => ['required', new ExistsModel(Student::class)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'billing_month' => ['nullable', 'max:20'],
            'note' => ['nullable'],
        ]);

        $student = Student::findOrFail($data['student_id']);
        $this->abortIfParentCannotPayForStudent($student);

        try {
            $payment = DB::transaction(function () use ($data, $khqr) {
                $payment = Payment::create([
                    'student_id' => $data['student_id'],
                    'recorded_by' => auth()->id(),
                    'provider' => 'khqr',
                    'receipt_no' => $this->nextKhqrReceipt(),
                    'payment_date' => now()->toDateString(),
                    'amount' => $data['amount'],
                    'method' => 'khqr',
                    'fee_type' => 'tuition',
                    'billing_month' => $data['billing_month'] ?? now()->format('Y-m'),
                    'status' => 'pending',
                    'verification_status' => 'pending',
                    'submitted_at' => now(),
                    'note' => $data['note'] ?? null,
                ]);

                $request = $khqr->createPaymentRequest($payment);

                $payment->update([
                    'khqr_payload' => $request['qr_data'],
                    'khqr_md5' => $request['md5'],
                    'khqr_expires_at' => $request['expires_at'],
                    'meta' => [
                        'khqr' => [
                            'provider' => $request['provider'],
                            'display_type' => $request['display_type'],
                            'reference' => $request['reference'],
                            'credential' => $request['credential'],
                            'account_name' => $request['account_name'],
                            'merchant_city' => $request['merchant_city'],
                            'raw_payload' => $request['raw_payload'],
                        ],
                    ],
                ]);

                return $payment->fresh(['student.classRoom', 'recorder']);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors(['khqr' => 'មិនអាចបង្កើត KHQR បានទេ។ សូមពិនិត្យការកំណត់ Bakong។']);
        }

        return redirect()->route('payments.show', $payment)->with('success', 'បានបង្កើត KHQR សម្រាប់ថ្លៃសិក្សា។');
    }

    public function checkKhqr(Request $request, Payment $payment, KHQRTuitionService $khqr)
    {
        $this->abortIfParentCannotView($payment);

        if (! $payment->isKhqr()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This payment is not KHQR.',
                ], 422);
            }

            return back()->withErrors(['khqr' => 'ការបង់ប្រាក់នេះមិនមែនជា KHQR ទេ។']);
        }

        if ($payment->status === 'paid') {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'paid',
                    'verification_status' => $payment->verification_status,
                    'transaction_id' => $payment->transaction_id,
                    'receipt_url' => route('payments.show', $payment),
                ]);
            }

            return back()->with('success', 'ការបង់ប្រាក់នេះបានបញ្ជាក់រួចហើយ។');
        }

        $expired = $khqr->hasExpired($payment);

        try {
            $response = $khqr->checkPaymentStatus($payment);
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'pending',
                    'verification_status' => 'pending',
                    'message' => 'Bakong confirmation is not available yet.',
                ], 202);
            }

            return back()->withErrors(['khqr' => 'មិនទាន់អាចបញ្ជាក់ពី Bakong បានទេ។ សូមសាកល្បងម្ដងទៀត។']);
        }

        if (! $khqr->isPaidResponse($response)) {
            if ($expired) {
                $payment->update([
                    'status' => 'expired',
                    'verification_status' => 'expired',
                    'verification_error' => $response['responseMessage'] ?? 'KHQR expired before Bakong confirmation.',
                    'meta' => array_merge($payment->meta ?? [], [
                        'expired_at' => now()->toDateTimeString(),
                        'last_khqr_check' => $response,
                    ]),
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'expired',
                        'message' => 'KHQR expired. Please create a new QR.',
                        'response' => $response,
                    ], 410);
                }

                return back()->withErrors(['khqr' => 'KHQR expired. Please create a new QR.']);
            }

            $payment->update([
                'verification_status' => 'pending',
                'verification_error' => $response['responseMessage'] ?? 'Pending',
                'meta' => array_merge($payment->meta ?? [], ['last_khqr_check' => $response]),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'pending',
                    'verification_status' => 'pending',
                    'message' => $response['responseMessage'] ?? 'Payment is still pending.',
                    'response' => $response,
                ], 202);
            }

            return back()->withErrors(['khqr' => 'Bakong មិនទាន់បញ្ជាក់ថាបានបង់ប្រាក់ទេ។']);
        }

        $payment->update([
            'status' => 'paid',
            'transaction_id' => $khqr->transactionIdFromResponse($response, $payment),
            'verification_status' => 'verified',
            'verification_error' => null,
            'verified_at' => now(),
            'confirmed_at' => now(),
            'payment_date' => now()->toDateString(),
            'meta' => array_merge($payment->meta ?? [], [
                'bakong_response' => $response,
                'confirmed_by' => 'bakong_md5',
            ]),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'paid',
                'verification_status' => 'verified',
                'transaction_id' => $payment->transaction_id,
                'receipt_url' => route('payments.show', $payment),
            ]);
        }

        return redirect()->route('payments.show', $payment)->with('success', 'Bakong បានបញ្ជាក់ការបង់ KHQR ជោគជ័យ។');
    }

    public function confirm(Payment $payment)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'accountant']), 403);

        $payment->update([
            'status' => 'paid',
            'transaction_id' => $payment->transaction_id ?: 'DIRECT-'.$payment->id,
            'verification_status' => $payment->isKhqr() ? 'manual_confirmed' : 'verified',
            'verification_error' => null,
            'verified_at' => now(),
            'confirmed_at' => now(),
            'payment_date' => now()->toDateString(),
            'meta' => array_merge($payment->meta ?? [], [
                'confirmed_by' => 'direct_cash',
                'confirmed_user_id' => auth()->id(),
                'confirmed_at' => now()->toDateTimeString(),
            ]),
        ]);

        return redirect()->route('payments.show', $payment)->with('success', 'បានបញ្ជាក់ការបង់ប្រាក់ផ្ទាល់ជោគជ័យ។');
    }

    public function show(Payment $payment)
    {
        $this->abortIfParentCannotView($payment);

        $payment->load(['student.classRoom', 'recorder']);

        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        return view('payments.form', $this->formData($payment));
    }

    public function update(Request $request, Payment $payment)
    {
        $payment->update($this->validated($request, $payment));

        return redirect()->route('payments.index')->with('success', 'កែប្រែការបង់ប្រាក់បានជោគជ័យ។');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('payments.index')->with('success', 'លុបការបង់ប្រាក់បានជោគជ័យ។');
    }

    private function formData(Payment $payment): array
    {
        return [
            'payment' => $payment,
            'students' => Student::orderBy('first_name')->get(),
            'statuses' => self::statuses(),
            'methods' => self::methods(),
            'feeTypes' => self::feeTypes(),
        ];
    }

    private function payableStudents()
    {
        return Student::query()
            ->when(auth()->user()->role === 'student_parent', fn ($query) => $query->where('user_id', auth()->id()))
            ->orderBy('first_name')
            ->get();
    }

    private function validated(Request $request, ?Payment $payment = null): array
    {
        return $request->validate([
            'student_id' => ['required', new ExistsModel(Student::class)],
            'receipt_no' => ['required', 'max:100', Rule::unique('payments', 'receipt_no')->ignore($payment?->getKey(), '_id')],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', Rule::in(array_keys(self::methods()))],
            'fee_type' => ['required', Rule::in(array_keys(self::feeTypes()))],
            'billing_month' => ['nullable', 'max:20'],
            'status' => ['required', Rule::in(array_keys(self::statuses()))],
            'note' => ['nullable'],
        ]);
    }

    private function abortIfParentCannotView(Payment $payment): void
    {
        if (in_array(auth()->user()->role, ['student', 'student_parent'], true) && $payment->student?->user_id !== auth()->id()) {
            abort(403, 'អ្នកមិនមានសិទ្ធិមើលបង្កាន់ដៃនេះទេ។');
        }
    }

    private function abortIfParentCannotPayForStudent(Student $student): void
    {
        if (in_array(auth()->user()->role, ['student', 'student_parent'], true) && $student->user_id !== auth()->id()) {
            abort(403, 'អ្នកមិនមានសិទ្ធិបង់ប្រាក់សម្រាប់សិស្សនេះទេ។');
        }
    }

    private function nextKhqrReceipt(): string
    {
        do {
            $receipt = 'KHQR-'.now()->format('Ymd-His').'-'.random_int(100, 999);
        } while (Payment::where('receipt_no', $receipt)->exists());

        return $receipt;
    }

    public static function statuses(): array
    {
        return [
            'pending' => 'កំពុងរង់ចាំ',
            'expired' => 'ផុតកំណត់',
            'paid' => 'បានបង់',
            'partial' => 'បង់មួយផ្នែក',
            'unpaid' => 'មិនទាន់បង់',
        ];
    }

    public static function methods(): array
    {
        return [
            'cash' => 'សាច់ប្រាក់',
            'khqr' => 'KHQR Bakong',
            'bank' => 'ធនាគារ',
            'card' => 'កាត',
            'online' => 'អនឡាញ',
        ];
    }

    public static function feeTypes(): array
    {
        return [
            'tuition' => 'ថ្លៃសិក្សា',
            'exam' => 'ថ្លៃប្រឡង',
            'material' => 'សម្ភារៈ',
            'other' => 'ផ្សេងៗ',
        ];
    }
}
