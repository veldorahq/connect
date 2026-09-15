<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe;

use Veldora\Framework\Foundation\ServiceProvider;
use Veldora\Framework\Config\Config;
use Veldora\Connect\Stripe\Exceptions\InvalidConfigException;

/**
 * StripeServiceProvider
 *
 * Registers the Stripe integration with the Veldora container.
 * Add this provider to your application's provider list:
 *
 * ```php
 * // bootstrap/app.php  (or wherever you register providers)
 * $app->registerProvider(\Veldora\Connect\Stripe\StripeServiceProvider::class);
 * ```
 *
 * After registration the following bindings become available:
 *  - `StripeClient::class`   — the low-level API wrapper
 *  - `StripeManager::class`  — the high-level service façade
 *  - `'stripe'` alias        — shorthand for `app('stripe')`
 *
 * The provider also merges `config/stripe.php` from this package so that
 * the config is available even without a published copy in the app.
 */
class StripeServiceProvider extends ServiceProvider
{
    /**
     * Register Stripe bindings into the container.
     *
     * @throws InvalidConfigException if the config cannot be resolved.
     */
    public function register(): void
    {
        // Merge the package default config if the app hasn't already set stripe config.
        $this->mergeConfig();

        // Register StripeClient as a singleton.
        $this->app->singleton(StripeClient::class, function () {
            /** @var Config $config */
            $config = $this->app->make(Config::class);

            /** @var array<string, mixed> $stripeConfig */
            $stripeConfig = $config->get('stripe', []);

            return new StripeClient($stripeConfig);
        });

        // Register StripeManager as a singleton that receives the StripeClient.
        $this->app->singleton(StripeManager::class, function () {
            /** @var StripeClient $client */
            $client = $this->app->make(StripeClient::class);
            return new StripeManager($client);
        });

        // Register the 'stripe' string alias so app('stripe') works.
        $this->app->singleton('stripe', function () {
            return $this->app->make(StripeManager::class);
        });
    }

    /**
     * Boot the service provider (nothing needed at boot time for Stripe).
     */
    public function boot(): void
    {
        // Intentionally empty — Stripe does not need boot-time setup.
    }

    /**
     * Merge the package's default config/stripe.php into the application config,
     * but only if the 'stripe' config key has not already been set by the app.
     */
    private function mergeConfig(): void
    {
        /** @var Config $config */
        $config = $this->app->make(Config::class);

        // Only load package defaults if no stripe config is present.
        if ($config->get('stripe') === null) {
            $packageConfig = require __DIR__ . '/../config/stripe.php';
            $config->set('stripe', $packageConfig);
        }
    }
}
