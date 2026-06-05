<?php

return [
    'provider' => env('KHQR_PROVIDER', 'khqr_link'),
    'bakong_account_id' => env('KHQR_BAKONG_ACCOUNT_ID'),
    'account_name' => env('KHQR_ACCOUNT_NAME', config('app.name', 'School')),
    'merchant_city' => env('KHQR_MERCHANT_CITY', 'PHNOM PENH'),
    'currency' => env('KHQR_CURRENCY', 'USD'),
    'api_token' => env('KHQR_API_TOKEN'),
    'bakong_fallback' => env('KHQR_BAKONG_FALLBACK', false),
    'link_api_base' => env('KHQR_LINK_API_BASE', 'https://api.khqr.link'),
    'link_purpose' => env('KHQR_LINK_PURPOSE', 'INVOICE'),
    'dynamic_qr_expires_in' => env('KHQR_DYNAMIC_QR_EXPIRES_IN', 600),
    'merchant_id' => env('KHQR_MERCHANT_ID'),
    'acquiring_bank' => env('KHQR_ACQUIRING_BANK'),
];
