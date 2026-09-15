<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Exceptions;

use RuntimeException;

/**
 * Base exception for all Veldora Stripe integration errors.
 *
 * Catch this type to handle any error originating from the
 * connect-stripe package regardless of specific subtype.
 */
class StripeException extends RuntimeException
{
}
