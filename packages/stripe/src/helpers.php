<?php

declare(strict_types=1);

use Veldora\Framework\Foundation\Application;
use Veldora\Connect\Stripe\StripeManager;

if (!function_exists('stripe')) {
    /**
     * Get the StripeManager instance or access its services.
     */
    function stripe(): StripeManager
    {
        return Application::getInstance()->get(StripeManager::class);
    }
}
