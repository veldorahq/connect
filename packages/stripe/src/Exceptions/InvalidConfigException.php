<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Exceptions;

/**
 * Thrown when Stripe configuration is missing or invalid.
 *
 * Example: missing STRIPE_SECRET_KEY in .env, or secret_key not set
 * in config/stripe.php.
 */
class InvalidConfigException extends StripeException
{
}
