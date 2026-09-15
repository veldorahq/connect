<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Services;

use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Veldora\Connect\Stripe\Exceptions\StripeException;
use Veldora\Connect\Stripe\StripeClient;

/**
 * Charge and Payment Intent service.
 *
 * Wraps the Stripe PaymentIntents API. In the modern Stripe flow,
 * PaymentIntents are the recommended way to accept payments.
 *
 * @see https://stripe.com/docs/api/payment_intents
 */
class ChargeService
{
    public function __construct(private readonly StripeClient $client)
    {
    }

    /**
     * Create a PaymentIntent.
     *
     * @param int                  $amount    Amount in the smallest currency unit (e.g. cents for USD).
     * @param string|null          $currency  ISO 4217 code. Defaults to the configured default currency.
     * @param array<string, mixed> $params    Additional Stripe parameters.
     * @throws StripeException
     */
    public function createPaymentIntent(
        int $amount,
        ?string $currency = null,
        array $params = []
    ): PaymentIntent {
        try {
            return $this->client->paymentIntents()->create(array_merge([
                'amount'   => $amount,
                'currency' => $currency ?? $this->client->defaultCurrency(),
            ], $params));
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Retrieve a PaymentIntent by ID.
     *
     * @throws StripeException
     */
    public function retrieve(string $paymentIntentId): PaymentIntent
    {
        try {
            return $this->client->paymentIntents()->retrieve($paymentIntentId);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Confirm a PaymentIntent (server-side confirmation).
     *
     * @param array<string, mixed> $params  Optional confirmation params (payment_method, etc.)
     * @throws StripeException
     */
    public function confirm(string $paymentIntentId, array $params = []): PaymentIntent
    {
        try {
            return $this->client->paymentIntents()->confirm($paymentIntentId, $params);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Capture an uncaptured PaymentIntent (for manual capture flows).
     *
     * @param array<string, mixed> $params
     * @throws StripeException
     */
    public function capture(string $paymentIntentId, array $params = []): PaymentIntent
    {
        try {
            return $this->client->paymentIntents()->capture($paymentIntentId, $params);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Cancel a PaymentIntent.
     *
     * @param array<string, mixed> $params  Optional cancellation reason params.
     * @throws StripeException
     */
    public function cancel(string $paymentIntentId, array $params = []): PaymentIntent
    {
        try {
            return $this->client->paymentIntents()->cancel($paymentIntentId, $params);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Update a PaymentIntent before it is confirmed.
     *
     * @param array<string, mixed> $params
     * @throws StripeException
     */
    public function update(string $paymentIntentId, array $params): PaymentIntent
    {
        try {
            return $this->client->paymentIntents()->update($paymentIntentId, $params);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
