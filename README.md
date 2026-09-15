# Veldora Connect

[![Latest Version](https://img.shields.io/badge/version-v0.7.0-blue.svg)](https://github.com/veldorahq/connect)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Veldora Connect** is the official integrations ecosystem for the [Veldora Framework](https://github.com/veldorahq/veldora). It brings seamless, battle-tested first-party integrations with essential third-party services, starting with payment gateways.

Starting with **v0.7.0**, Veldora Connect officially introduces the **Stripe Payment Gateway** integration!

---

## Packages

| Package | Status | Description |
|---|---|---|
| [`veldora/connect-stripe`](packages/stripe) | **Active (v0.7.0)** | Official Stripe payment gateway (Checkout, PaymentIntents, Webhooks, Customers) |
| `veldora/connect-sslcommerz` | *Upcoming (Roadmap)* | SSLCommerz payment gateway integration |
| `veldora/connect-resend` | *Upcoming (Roadmap)* | Resend transactional email service |
| `veldora/connect-s3` | *Upcoming (Roadmap)* | AWS S3 / S3-compatible cloud storage driver |
| `veldora/connect-sentry` | *Upcoming (Roadmap)* | Sentry real-time crash reporting and telemetry |

---

## Getting Started with Stripe

Install the official Stripe integration package via Composer:

```bash
composer require veldora/connect-stripe
```

### 1. Publish Configuration & Environment

Add your Stripe API keys to your `.env` file:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_CURRENCY=usd
```

Register the service provider in `config/app.php` (if not auto-discovered):

```php
'providers' => [
    // ...
    Veldora\Connect\Stripe\StripeServiceProvider::class,
],
```

### 2. Basic Checkout Session Example

```php
use Veldora\Connect\Stripe\Facades\Stripe;

Route::post('/checkout', function () {
    $session = Stripe::checkout()->create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Veldora Pro Subscription',
                ],
                'unit_amount' => 2000,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => url('/success?session_id={CHECKOUT_SESSION_ID}'),
        'cancel_url' => url('/cancel'),
    ]);

    return redirect($session->url);
});
```

### 3. Handling Webhooks

```php
use Veldora\Connect\Stripe\Facades\Stripe;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;

Route::post('/webhook/stripe', function (Request $request) {
    $payload = $request->getContent();
    $sigHeader = $request->header('stripe-signature');

    try {
        $event = Stripe::webhook()->constructEvent(
            $payload,
            $sigHeader,
            config('stripe.webhook_secret')
        );

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            // Fulfill the order
        }

        return Response::json(['status' => 'success']);
    } catch (\Exception $e) {
        return Response::json(['error' => $e->getMessage()], 400);
    }
});
```

---

## Monorepo Structure

```
veldora-connect/
├── packages/
│   └── stripe/            # veldora/connect-stripe (v0.7.0)
├── CHANGELOG.md           # Release history
├── README.md              # Documentation & guide
└── composer.json          # Monorepo root config
```

---

## License

Veldora Connect is open-source software licensed under the [MIT License](LICENSE).
