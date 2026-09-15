<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Facades;

use Veldora\Framework\Foundation\Application;
use Veldora\Connect\Stripe\StripeManager;

/**
 * Stripe Facade for Veldora Applications.
 *
 * Provides static access to the StripeManager singleton.
 *
 * @method static \Veldora\Connect\Stripe\Services\CheckoutService checkout()
 * @method static \Veldora\Connect\Stripe\Services\CustomerService customers()
 * @method static \Veldora\Connect\Stripe\Services\SubscriptionService subscriptions()
 * @method static \Veldora\Connect\Stripe\Services\ChargeService charges()
 * @method static \Veldora\Connect\Stripe\Services\WebhookService webhooks()
 * @method static \Veldora\Connect\Stripe\StripeClient client()
 */
class Stripe
{
    /**
     * Dynamically proxy static calls to the StripeManager singleton.
     *
     * @param array<mixed> $parameters
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        /** @var StripeManager $manager */
        $manager = Application::getInstance()->get(StripeManager::class);
        $callable = [$manager, $method];
        return $callable(...$parameters);
    }
}
