<?php

namespace App\Services;

use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
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

    public function checkPaymentStatus(Payment $payment): array
    {
        $token = $this->required('KHQR_API_TOKEN', config('khqr.api_token'));

        if (blank($payment->khqr_md5)) {
            throw new \RuntimeException('Payment does not have a KHQR MD5 hash.');
        }

        return (new BakongKHQR($token))->checkTransactionByMD5($payment->khqr_md5);
    }

    public function hasExpired(Payment $payment): bool
    {
        return $payment->khqr_expires_at !== null && now()->greaterThan($payment->khqr_expires_at);
    }

    public function isPaidResponse(array $response): bool
    {
        if (($response['responseCode'] ?? null) !== 0) {
            return false;
        }

        if (array_key_exists('verified', $response)) {
            return ($response['verified'] ?? false) === true
                && strtoupper((string) ($response['status'] ?? '')) === 'COMPLETED';
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

    private function required(string $key, mixed $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            throw new \RuntimeException("Missing {$key}.");
        }

        return $value;
    }
}
