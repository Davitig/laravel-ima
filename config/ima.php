<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Integrated Merchant Agent (IMA)
    |--------------------------------------------------------------------------
    |
    | This keys are used to configure Integrated Merchant Agent (IMA).
    |
    */

    'merchant_handler' => env('IMA_MERCHANT_HANDLER', ''), // 'https://example.com/MerchantHandler',
    'client_handler' => env('IMA_CLIENT_HANDLER', ''), // 'https://example.com/ClientHandler',
    'cert_path' => env('IMA_CERT_PATH', ''),
    'key_path' => env('IMA_KEY_PATH', ''),
    'password' => env('IMA_PASS', ''),
    // Transaction currency code (ISO 4217), mandatory, (3 digits).
    'currency' => env('IMA_CURRENCY', 840) // USD

];
