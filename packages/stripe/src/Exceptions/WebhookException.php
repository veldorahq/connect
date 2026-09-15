<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Exceptions;

/**
 * Thrown when a Stripe webhook signature verification fails.
 *
 * This typically means the webhook payload was tampered with,
 * the wrong secret is configured, or the event is too old.
 */
class WebhookException extends StripeException
{
}
