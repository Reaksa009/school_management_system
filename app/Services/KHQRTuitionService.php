<?php

namespace App\Services;

use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Helpers\Utils;
use KHQR\Models\IndividualInfo;
use KHQR\Models\MerchantInfo;

class KHQRTuitionService
{
    public function createPaymentRequest(Payment $payment): array
    {
        $payment->loadMissing('student.classRoom');

        if ($this->usesKhqrLink()) {
            return $this->createKhqrLinkPaymentRequest($payment);
        }

        return $this->createLocalPaymentRequest($payment);
    }

    private function createLocalPaymentRequest(Payment $payment): array
    {
        $accountId = $this->required('KHQR_BAKONG_ACCOUNT_ID', config('khqr.bakong_account_id'));
        $accountName = (string) config('khqr.account_name', config('app.name'));
        $merchantCity = (string) config('khqr.merchant_city', 'PHNOM PENH');
        $currency = $this->currencyCode((string) config('khqr.currency', 'USD'));
        $amount = $this->formatAmount((float) $payment->amount, $currency);
        $reference = substr($payment->receipt_no, 0, 25);
        $createdAt = now();
        $expiresAt = $createdAt->copy()->addSeconds($this->expiresInSeconds());

        $optionalData = [
            'currency' => $currency,
            'amount' => $amount,
            'billNumber' => $reference,
            'purposeOfTransaction' => $this->purpose($payment),
        ];

        $merchantId = config('khqr.merchant_id');
        $acquiringBank = config('khqr.acquiring_bank');

        if ($merchantId && $acquiringBank) {
            $info = MerchantInfo::withOptionalArray(
                $accountId,
                $accountName,
                $merchantCity,
                $merchantId,
                $acquiringBank,
                $optionalData
            );
            $response = BakongKHQR::generateMerchant($info);
        } else {
            $info = IndividualInfo::withOptionalArray(
                $accountId,
                $accountName,
                $merchantCity,
                $optionalData
            );
            $response = BakongKHQR::generateIndividual($info);
        }

        if ((int) Arr::get($response->status, 'code') !== 0 || blank($response->data['qr'] ?? null)) {
            throw new \RuntimeException(Arr::get($response->status, 'message') ?: 'Bakong KHQR generator failed.');
        }

        $payload = (string) $response->data['qr'];
        $payload = $this->withDynamicExpiration($payload, $createdAt, $expiresAt);

        return [
            'provider' => 'khqr',
            'display_type' => 'payload',
            'qr_data' => $payload,
            'md5' => md5($payload),
            'reference' => $reference,
            'expires_at' => $expiresAt,
            'credential' => $accountId,
            'account_name' => $accountName,
            'merchant_city' => $merchantCity,
            'raw_payload' => [
                'bakong_account_id' => $accountId,
                'merchant_name' => $accountName,
                'merchant_city' => $merchantCity,
                'amount' => $amount,
                'currency' => $currency === KHQRData::CURRENCY_KHR ? 'KHR' : 'USD',
                'reference' => $reference,
                'description' => $this->purpose($payment),
                'created_timestamp' => $this->timestampInMilliseconds($createdAt),
                'expiration_timestamp' => $this->timestampInMilliseconds($expiresAt),
                'student' => $payment->student?->full_name,
                'class' => $payment->student?->classRoom?->name,
                'created_at' => now()->toIso8601String(),
                'expires_at' => $expiresAt->toIso8601String(),
            ],
        ];
    }

    private function createKhqrLinkPaymentRequest(Payment $payment): array
    {
        $accountId = $this->required('KHQR_BAKONG_ACCOUNT_ID', config('khqr.bakong_account_id'));
        $accountName = $this->required('KHQR_ACCOUNT_NAME', config('khqr.account_name', config('app.name')));
        $apiBase = rtrim($this->required('KHQR_LINK_API_BASE', config('khqr.link_api_base')), '/');
        $currency = strtoupper((string) config('khqr.currency', 'USD'));
        $reference = substr($payment->receipt_no, 0, 25);
        $purpose = strtoupper((string) config('khqr.link_purpose', 'INVOICE'));
        $metadata = [
            'source' => 'school_management_system',
            'receipt_no' => $payment->receipt_no,
            'student_id' => (string) $payment->student_id,
            'student_code' => $payment->student?->student_code,
            'student_name' => $payment->student?->full_name,
            'class' => $payment->student?->classRoom?->name,
            'billing_month' => $payment->billing_month,
        ];

        $response = Http::timeout(15)
            ->retry(2, 250)
            ->acceptJson()
            ->get($apiBase.'/v1/khqr/create', [
                'amount' => $this->formatAmount((float) $payment->amount, $this->currencyCode($currency)),
                'bakongid' => $accountId,
                'merchantname' => $accountName,
                'purpose' => in_array($purpose, ['GENERAL', 'INVOICE', 'SUBSCRIPTION', 'ADDON'], true) ? $purpose : 'INVOICE',
                'reference_type' => 'invoice',
                'reference_id' => $this->numericReferenceId($payment),
                'reference_code' => $reference,
                'metadata' => json_encode($metadata, JSON_UNESCAPED_SLASHES),
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException($response->json('error') ?: 'KHQR Link API request failed.');
        }

        $data = $response->json();

        if (($data['status'] ?? null) !== 'success' || blank($data['qr'] ?? null) || blank($data['md5'] ?? null)) {
            throw new \RuntimeException($data['error'] ?? 'KHQR Link API returned an invalid response.');
        }

        $qrUrl = $this->secureQrUrl((string) $data['qr']);
        $expiresAt = filled($data['expires_at'] ?? null)
            ? Carbon::parse($data['expires_at'])
            : now()->addSeconds($this->expiresInSeconds());

        return [
            'provider' => 'khqr_link',
            'display_type' => 'image_url',
            'qr_data' => $qrUrl,
            'md5' => (string) $data['md5'],
            'reference' => $reference,
            'expires_at' => $expiresAt,
            'credential' => $accountId,
            'account_name' => $accountName,
            'merchant_city' => (string) config('khqr.merchant_city', 'PHNOM PENH'),
            'raw_payload' => [
                'api_base' => $apiBase,
                'merchant_name' => $accountName,
                'bakong_account_id' => $accountId,
                'amount' => $data['amount'] ?? (float) $payment->amount,
                'currency' => $data['currency'] ?? $currency,
                'reference' => $reference,
                'reference_id' => $data['reference_id'] ?? $this->numericReferenceId($payment),
                'reference_code' => $data['reference_code'] ?? $reference,
                'tran' => $data['tran'] ?? null,
                'qr' => $qrUrl,
                'md5' => $data['md5'],
                'purpose' => $data['purpose'] ?? $purpose,
                'metadata' => $metadata,
                'created_at' => $data['created_at'] ?? now()->toIso8601String(),
                'expires_at' => $expiresAt->toIso8601String(),
            ],
        ];
    }

    public function checkPaymentStatus(Payment $payment): array
    {
        if ($this->usesKhqrLink($payment)) {
            return $this->checkKhqrLinkPaymentStatus($payment);
        }

        $token = $this->required('KHQR_API_TOKEN', config('khqr.api_token'));

        if (blank($payment->khqr_md5)) {
            throw new \RuntimeException('Payment does not have a KHQR MD5 hash.');
        }

        return (new BakongKHQR($token))->checkTransactionByMD5($payment->khqr_md5);
    }

    private function checkKhqrLinkPaymentStatus(Payment $payment): array
    {
        if (blank($payment->khqr_md5)) {
            throw new \RuntimeException('Payment does not have a KHQR MD5 hash.');
        }

        $apiBase = rtrim($this->required('KHQR_LINK_API_BASE', data_get($payment->meta, 'khqr.raw_payload.api_base') ?: config('khqr.link_api_base')), '/');

        $response = Http::timeout(15)
            ->retry(2, 250)
            ->acceptJson()
            ->get($apiBase.'/v1/khqr/check', [
                'md5' => $payment->khqr_md5,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException($response->json('error') ?: 'KHQR Link check request failed.');
        }

        return $response->json();
    }

    public function hasExpired(Payment $payment): bool
    {
        return $payment->khqr_expires_at !== null && now()->greaterThan($payment->khqr_expires_at);
    }

    public function isPaidResponse(array $response): bool
    {
        $verified = filter_var($response['verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $status = strtoupper((string) ($response['status'] ?? ''));

        if (array_key_exists('verified', $response)) {
            return $verified && $status === 'COMPLETED';
        }

        if (array_key_exists('responseCode', $response)) {
            return (string) $response['responseCode'] === '0';
        }

        return true;
    }

    public function transactionIdFromResponse(array $response, Payment $payment): string
    {
        return data_get($response, 'data.hash')
            ?? data_get($response, 'hash')
            ?? data_get($response, 'tran')
            ?? data_get($response, 'data.transactionHash')
            ?? 'KHQR-'.$payment->id;
    }

    private function usesKhqrLink(?Payment $payment = null): bool
    {
        if ($payment) {
            $provider = data_get($payment->meta, 'khqr.provider');

            if ($provider) {
                return $provider === 'khqr_link';
            }

            return str_starts_with((string) $payment->khqr_payload, 'http://api.khqr.link/')
                || str_starts_with((string) $payment->khqr_payload, 'https://api.khqr.link/');
        }

        return config('khqr.provider') === 'khqr_link';
    }

    private function purpose(Payment $payment): string
    {
        $studentCode = $payment->student?->student_code ?: 'student';
        $month = $payment->billing_month ?: now()->format('Y-m');

        return substr("Tuition {$studentCode} {$month}", 0, 25);
    }

    private function expiresInSeconds(): int
    {
        $seconds = (int) config('khqr.dynamic_qr_expires_in', 600);

        return max(60, min($seconds, 600));
    }

    private function withDynamicExpiration(string $qrData, CarbonInterface $createdAt, CarbonInterface $expiresAt): string
    {
        $crcTagPosition = strlen($qrData) - 8;

        if ($crcTagPosition < 0 || substr($qrData, $crcTagPosition, 4) !== '6304') {
            throw new \RuntimeException('Generated KHQR payload has an invalid CRC segment.');
        }

        $payload = substr($qrData, 0, $crcTagPosition);
        $timestamp = $this->timestampSegment($createdAt, $expiresAt);
        $bounds = $this->findTimestampBounds($payload);

        if ($bounds) {
            [$start, $end] = $bounds;
            $payload = substr($payload, 0, $start).$timestamp.substr($payload, $end);
        } else {
            $payload .= $timestamp;
        }

        $payloadWithCrcTag = $payload.'6304';

        return $payloadWithCrcTag.Utils::crc16($payloadWithCrcTag);
    }

    private function findTimestampBounds(string $payload): ?array
    {
        $offset = 0;
        $length = strlen($payload);

        while ($offset + 4 <= $length) {
            $tag = substr($payload, $offset, 2);
            $valueLength = (int) substr($payload, $offset + 2, 2);
            $nextOffset = $offset + 4 + $valueLength;

            if ($nextOffset > $length) {
                throw new \RuntimeException('Generated KHQR payload has invalid TLV lengths.');
            }

            if ($tag === '99') {
                return [$offset, $nextOffset];
            }

            $offset = $nextOffset;
        }

        return null;
    }

    private function timestampSegment(CarbonInterface $createdAt, CarbonInterface $expiresAt): string
    {
        $value = '0013'.$this->timestampInMilliseconds($createdAt)
            .'0113'.$this->timestampInMilliseconds($expiresAt);

        return '99'.str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT).$value;
    }

    private function timestampInMilliseconds(CarbonInterface $time): string
    {
        return (string) (((int) $time->format('U')) * 1000 + (int) floor(((int) $time->format('u')) / 1000));
    }

    private function currencyCode(string $currency): int
    {
        return strtoupper($currency) === 'KHR'
            ? KHQRData::CURRENCY_KHR
            : KHQRData::CURRENCY_USD;
    }

    private function formatAmount(float $amount, int $currency): float
    {
        if ($currency === KHQRData::CURRENCY_KHR) {
            return (float) round($amount);
        }

        return (float) number_format($amount, 2, '.', '');
    }

    private function numericReferenceId(Payment $payment): int
    {
        return (int) sprintf('%u', crc32((string) $payment->getKey()));
    }

    private function secureQrUrl(string $url): string
    {
        return preg_replace('/^http:\/\/api\.khqr\.link\//i', 'https://api.khqr.link/', $url) ?: $url;
    }

    private function required(string $key, mixed $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            throw new \RuntimeException("Missing {$key}.");
        }

        return $value;
    }
}
