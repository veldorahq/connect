<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Veldora\Connect\Stripe\StripeClient;
use Veldora\Connect\Stripe\StripeManager;
use Veldora\Connect\Stripe\Services\ChargeService;
use Veldora\Connect\Stripe\Services\CheckoutService;
use Veldora\Connect\Stripe\Services\CustomerService;
use Veldora\Connect\Stripe\Services\SubscriptionService;
use Veldora\Connect\Stripe\Services\WebhookService;

/**
 * Unit tests for StripeManager.
 *
 * Verifies that the manager correctly constructs and caches service instances,
 * and that currency/public-key helpers delegate to the underlying StripeClient.
 */
class StripeManagerTest extends TestCase
{
    private StripeClient $client;
    private StripeManager $manager;

    protected function setUp(): void
    {
        $this->client = new StripeClient([
            'mode'       => 'test',
            'secret_key' => 'sk_test_managertest',
            'public_key' => 'pk_test_managertest',
            'currency'   => 'eur',
            'webhook'    => ['secret' => 'whsec_mgr', 'tolerance' => 300],
        ]);

        $this->manager = new StripeManager($this->client);
    }

    // -------------------------------------------------------------------------
    // Service accessors return correct types
    // -------------------------------------------------------------------------

    public function test_charges_returns_charge_service(): void
    {
        $this->assertInstanceOf(ChargeService::class, $this->manager->charges());
    }

    public function test_checkout_returns_checkout_service(): void
    {
        $this->assertInstanceOf(CheckoutService::class, $this->manager->checkout());
    }

    public function test_customers_returns_customer_service(): void
    {
        $this->assertInstanceOf(CustomerService::class, $this->manager->customers());
    }

    public function test_subscriptions_returns_subscription_service(): void
    {
        $this->assertInstanceOf(SubscriptionService::class, $this->manager->subscriptions());
    }

    public function test_webhooks_returns_webhook_service(): void
    {
        $this->assertInstanceOf(WebhookService::class, $this->manager->webhooks());
    }

    // -------------------------------------------------------------------------
    // Service instances are cached (same object on repeated calls)
    // -------------------------------------------------------------------------

    public function test_charges_is_cached(): void
    {
        $this->assertSame($this->manager->charges(), $this->manager->charges());
    }

    public function test_checkout_is_cached(): void
    {
        $this->assertSame($this->manager->checkout(), $this->manager->checkout());
    }

    public function test_customers_is_cached(): void
    {
        $this->assertSame($this->manager->customers(), $this->manager->customers());
    }

    public function test_subscriptions_is_cached(): void
    {
        $this->assertSame($this->manager->subscriptions(), $this->manager->subscriptions());
    }

    public function test_webhooks_is_cached(): void
    {
        $this->assertSame($this->manager->webhooks(), $this->manager->webhooks());
    }

    // -------------------------------------------------------------------------
    // Convenience helpers
    // -------------------------------------------------------------------------

    public function test_currency_returns_configured_default(): void
    {
        $this->assertSame('eur', $this->manager->currency());
    }

    public function test_default_currency_returns_configured_default(): void
    {
        $this->assertSame('eur', $this->manager->defaultCurrency());
    }

    public function test_public_key_returns_configured_key(): void
    {
        $this->assertSame('pk_test_managertest', $this->manager->publicKey());
    }

    public function test_mode_returns_configured_mode(): void
    {
        $this->assertSame('test', $this->manager->mode());
    }

    public function test_client_returns_stripe_client_instance(): void
    {
        $this->assertSame($this->client, $this->manager->client());
    }
}
