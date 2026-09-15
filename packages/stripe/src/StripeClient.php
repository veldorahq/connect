<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe;

use Stripe\StripeClient as SdkClient;
use Veldora\Connect\Stripe\Exceptions\InvalidConfigException;

/**
 * Veldora-native Stripe client wrapper.
 *
 * Wraps the official Stripe PHP SDK client and exposes it through a
 * Veldora-idiomatic interface. Validates configuration on construction
 * so failures are caught at boot time, not at the point of use.
 *
 * Usage (via container):
 *   $client = app(StripeClient::class);
 *   $customer = $client->customers()->create(['email' => 'user@example.com']);
 *
 * Usage (via StripeManager — preferred):
 *   $customer = stripe()->customers()->create(['email' => 'user@example.com']);
 */
class StripeClient
{
    /**
     * The underlying Stripe SDK client.
     */
    private SdkClient $client;

    /**
     * The resolved configuration array.
     *
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * Create a new StripeClient instance.
     *
     * @param array<string, mixed> $config
     *
     * @throws InvalidConfigException if the secret key is missing or clearly invalid.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->validate();

        $this->client = new SdkClient([
            'api_key' => $config['secret_key'],
        ]);
    }

    // -------------------------------------------------------------------------
    // Stripe SDK resource proxies
    // -------------------------------------------------------------------------

    /**
     * Access the Customers resource.
     *
     * @return \Stripe\Service\CustomerService
     */
    public function customers(): \Stripe\Service\CustomerService
    {
        return $this->client->customers;
    }

    /**
     * Access the PaymentIntents resource.
     *
     * @return \Stripe\Service\PaymentIntentService
     */
    public function paymentIntents(): \Stripe\Service\PaymentIntentService
    {
        return $this->client->paymentIntents;
    }

    /**
     * Access the Checkout Sessions resource.
     *
     * @return \Stripe\Service\Checkout\SessionService
     */
    public function checkout(): \Stripe\Service\Checkout\SessionService
    {
        return $this->client->checkout->sessions;
    }

    /**
     * Access the Subscriptions resource.
     *
     * @return \Stripe\Service\SubscriptionService
     */
    public function subscriptions(): \Stripe\Service\SubscriptionService
    {
        return $this->client->subscriptions;
    }

    /**
     * Access the Refunds resource.
     *
     * @return \Stripe\Service\RefundService
     */
    public function refunds(): \Stripe\Service\RefundService
    {
        return $this->client->refunds;
    }

    /**
     * Access the Prices resource.
     *
     * @return \Stripe\Service\PriceService
     */
    public function prices(): \Stripe\Service\PriceService
    {
        return $this->client->prices;
    }

    /**
     * Access the Products resource.
     *
     * @return \Stripe\Service\ProductService
     */
    public function products(): \Stripe\Service\ProductService
    {
        return $this->client->products;
    }

    /**
     * Access the Invoices resource.
     *
     * @return \Stripe\Service\InvoiceService
     */
    public function invoices(): \Stripe\Service\InvoiceService
    {
        return $this->client->invoices;
    }

    // -------------------------------------------------------------------------
    // Config helpers
    // -------------------------------------------------------------------------

    /**
     * Get the configured default currency.
     */
    public function defaultCurrency(): string
    {
        return (string) ($this->config['currency'] ?? 'usd');
    }

    /**
     * Get the configured mode: 'test' or 'live'.
     */
    public function mode(): string
    {
        return (string) ($this->config['mode'] ?? 'test');
    }

    /**
     * Get the publishable key (safe for client-side).
     */
    public function publicKey(): string
    {
        return (string) ($this->config['public_key'] ?? '');
    }

    /**
     * Retrieve the webhook configuration subset.
     *
     * @return array{secret: string, tolerance: int}
     */
    public function webhookConfig(): array
    {
        return [
            'secret'    => (string) ($this->config['webhook']['secret'] ?? ''),
            'tolerance' => (int) ($this->config['webhook']['tolerance'] ?? 300),
        ];
    }

    /**
     * Retrieve the raw Stripe PHP SDK client for advanced use cases.
     *
     * Use only when the higher-level Veldora API does not cover your
     * requirement. Direct SDK usage bypasses Veldora error normalization.
     */
    public function getClient(): SdkClient
    {
        return $this->client;
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    /**
     * Validate the configuration before initializing the SDK client.
     *
     * @throws InvalidConfigException
     */
    private function validate(): void
    {
        $key  = $this->config['secret_key'] ?? '';
        $mode = (string) ($this->config['mode'] ?? 'test');

        if (!is_string($key) || trim($key) === '') {
            throw new InvalidConfigException(
                'STRIPE_SECRET_KEY is not set. '
                . 'Add it to your .env file (sk_test_... for test mode, sk_live_... for live mode).'
            );
        }

        $validModes = ['test', 'live'];
        if (!in_array($mode, $validModes, true)) {
            throw new InvalidConfigException(
                'Invalid STRIPE_MODE "' . $mode . '". Supported values: test, live.'
            );
        }

        // Warn if a live key is used in test mode or vice versa.
        if ($mode === 'test' && str_starts_with($key, 'sk_live_')) {
            throw new InvalidConfigException(
                'STRIPE_MODE is set to "test" but STRIPE_SECRET_KEY starts with "sk_live_". '
                . 'Change STRIPE_MODE=live or use a test-mode key (sk_test_...).'
            );
        }
        if ($mode === 'live' && str_starts_with($key, 'sk_test_')) {
            throw new InvalidConfigException(
                'STRIPE_MODE is set to "live" but STRIPE_SECRET_KEY starts with "sk_test_". '
                . 'Change STRIPE_MODE=test or use a live-mode key (sk_live_...).'
            );
        }
    }
}
