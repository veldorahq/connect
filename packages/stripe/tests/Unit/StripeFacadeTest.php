<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Tests\Unit;

require_once __DIR__ . '/../../src/helpers.php';

use PHPUnit\Framework\TestCase;
use Veldora\Connect\Stripe\Facades\Stripe;
use Veldora\Connect\Stripe\StripeManager;
use Veldora\Connect\Stripe\StripeServiceProvider;
use Veldora\Framework\Config\Config;
use Veldora\Framework\Foundation\Application;

class StripeFacadeTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(__DIR__ . '/../../');
        $this->app->singleton(Config::class, function () {
            $config = new Config('');
            $config->set('stripe', [
                'secret_key'      => 'sk_test_123',
                'public_key'      => 'pk_test_123',
                'webhook_secret'  => 'whsec_123',
                'currency'        => 'usd',
            ]);
            return $config;
        });

        $this->app->registerProvider(StripeServiceProvider::class);
    }

    public function test_stripe_helper_returns_stripe_manager(): void
    {
        $manager = \stripe();
        $this->assertInstanceOf(StripeManager::class, $manager);
    }

    public function test_stripe_facade_proxies_to_manager(): void
    {
        $checkout = Stripe::checkout();
        $this->assertInstanceOf(\Veldora\Connect\Stripe\Services\CheckoutService::class, $checkout);

        $customers = Stripe::customers();
        $this->assertInstanceOf(\Veldora\Connect\Stripe\Services\CustomerService::class, $customers);

        $webhooks = Stripe::webhooks();
        $this->assertInstanceOf(\Veldora\Connect\Stripe\Services\WebhookService::class, $webhooks);
    }
}
