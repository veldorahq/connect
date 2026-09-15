<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe;

use Veldora\Connect\Stripe\Services\ChargeService;
use Veldora\Connect\Stripe\Services\CheckoutService;
use Veldora\Connect\Stripe\Services\CustomerService;
use Veldora\Connect\Stripe\Services\SubscriptionService;
use Veldora\Connect\Stripe\Services\WebhookService;

/**
 * Stripe Manager — the primary Veldora integration entry point.
 *
 * Provides lazily-instantiated access to all service objects.
 * Registered as a singleton in the container under both
 * `StripeManager::class` and the `'stripe'` alias.
 *
 * Usage via helper:
 *   stripe()->customers()->create(['email' => 'user@example.com']);
 *
 * Usage via container:
 *   $manager = app(StripeManager::class);
 *   $pi = $manager->charges()->createPaymentIntent(1000, 'usd');
 *
 * Usage via direct property access:
 *   $customer = app('stripe')->customers->create([...]);
 */
class StripeManager
{
    /**
     * Lazily-instantiated service singletons.
     *
     * @var array<string, object>
     */
    private array $services = [];

    /**
     * Create a new StripeManager.
     */
    public function __construct(private readonly StripeClient $client)
    {
    }

    // -------------------------------------------------------------------------
    // Service accessors (lazy singletons)
    // -------------------------------------------------------------------------

    /**
     * Get the Customer service.
     */
    public function customers(): CustomerService
    {
        return $this->service(CustomerService::class);
    }

    /**
     * Get the Charge / PaymentIntent service.
     */
    public function charges(): ChargeService
    {
        return $this->service(ChargeService::class);
    }

    /**
     * Get the Checkout Session service.
     */
    public function checkout(): CheckoutService
    {
        return $this->service(CheckoutService::class);
    }

    /**
     * Get the Subscription service.
     */
    public function subscriptions(): SubscriptionService
    {
        return $this->service(SubscriptionService::class);
    }

    /**
     * Get the Webhook service.
     */
    public function webhooks(): WebhookService
    {
        return $this->service(WebhookService::class);
    }

    // -------------------------------------------------------------------------
    // Passthrough helpers
    // -------------------------------------------------------------------------

    /**
     * Get the configured publishable (public) key for use in JavaScript.
     */
    public function publicKey(): string
    {
        return $this->client->publicKey();
    }

    /**
     * Get the configured mode: 'test' or 'live'.
     */
    public function mode(): string
    {
        return $this->client->mode();
    }

    /**
     * Get the default currency code.
     */
    public function defaultCurrency(): string
    {
        return $this->client->defaultCurrency();
    }

    /**
     * Alias for defaultCurrency() — shorter form for templates and helpers.
     */
    public function currency(): string
    {
        return $this->client->defaultCurrency();
    }

    /**
     * Access the underlying StripeClient for advanced use cases.
     */
    public function client(): StripeClient
    {
        return $this->client;
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    /**
     * Resolve a service class lazily, constructing it once per manager lifetime.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function service(string $class): object
    {
        if (!isset($this->services[$class])) {
            $this->services[$class] = new $class($this->client);
        }

        /** @var T */
        return $this->services[$class];
    }
}
