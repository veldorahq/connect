<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Stripe Configuration
|--------------------------------------------------------------------------
|
| Configure your Stripe integration. Set the keys in your .env file.
| To publish this config file to your app, run:
|
|   php veldora connect:stripe:publish
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe Mode
    |--------------------------------------------------------------------------
    | 'test' uses test-mode keys (sk_test_...).
    | 'live' uses live-mode keys (sk_live_...).
    */
    'mode' => env('STRIPE_MODE', 'test'),

    /*
    |--------------------------------------------------------------------------
    | Secret Key
    |--------------------------------------------------------------------------
    | Your Stripe secret API key. Keep this private — never expose it in
    | client-side code. Use test-mode keys (sk_test_...) during development.
    */
    'secret_key' => env('STRIPE_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Publishable Key
    |--------------------------------------------------------------------------
    | Your Stripe publishable key for use in client-side JavaScript.
    | Safe to expose (pk_test_... or pk_live_...).
    */
    'public_key' => env('STRIPE_PUBLIC_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    | ISO 4217 three-letter currency code.
    */
    'currency' => env('STRIPE_DEFAULT_CURRENCY', 'usd'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    | secret   — From your Stripe Dashboard webhook endpoint settings (whsec_...).
    | tolerance — Maximum seconds the event timestamp may differ from now.
    */
    'webhook' => [
        'secret'    => env('STRIPE_WEBHOOK_SECRET', ''),
        'tolerance' => (int) env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Options
    |--------------------------------------------------------------------------
    | Passed directly to the Stripe PHP SDK client.
    */
    'http' => [
        'timeout' => 30,
    ],

];
