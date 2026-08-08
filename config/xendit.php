<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Xendit Secret Key
    |--------------------------------------------------------------------------
    | Digunakan untuk create invoice API
    */
    'secret_key' => env('XENDIT_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Xendit Webhook Token
    |--------------------------------------------------------------------------
    | Digunakan untuk verifikasi webhook callback
    */
    'webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Callback URL (optional)
    |--------------------------------------------------------------------------
    */
    'invoice_callback_url' => env('XENDIT_INVOICE_CALLBACK_URL'),
];
