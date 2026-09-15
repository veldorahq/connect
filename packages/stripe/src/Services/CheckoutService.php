<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Services;

use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Veldora\Connect\Stripe\Exceptions\StripeException;
use Veldora\Connect\Stripe\StripeClient;

/**
 * Checkout Session service.
 *
 * Wraps the Stripe Checkout Sessions API for hosted payment pages.
 * This is the simplest way to accept one-time or recurring payments —
 * redirect users to a Stripe-hosted checkout page.
 *
 * @see https://stripe.com/docs/api/checkout/sessions
 */
class CheckoutService
{
    public function __construct(private readonly StripeClient $client)
    {
    }

    /**
     * Create a new Checkout Session.
     *
     * Minimal usage (one-time payment):
     * ```php
     * $session = stripe()->checkout()->createSession([
     *     'success_url' => 'https://example.com/success',
     *     'cancel_url'  => 'https://example.com/cancel',
     *     'line_items'  => [[
     *         'price_data' => [
     *             'currency'     => 'usd',
     *             'unit_amount'  => 2000,
     *             'product_data' => ['name' => 'Widget'],
     *         ],
     *         'quantity' => 1,
     *     ]],
     * ]);
     * return redirect($session->url);
     * ```
     *
     * @param array<string, mixed> $params  Stripe checkout session params.
     * @throws StripeException
     */
    public function createSession(array $params): Session
    {
        // Default to 'payment' mode when not specified.
        if (!isset($params['mode'])) {
            $params['mode'] = 'payment';
        }

        try {
            return $this->client->checkout()->create($params);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Retrieve a Checkout Session by ID.
     *
     * @param array<string, mixed> $params  Optional expand params.
     * @throws StripeException
     */
    public function retrieve(string $sessionId, array $params = []): Session
    {
        try {
            return $this->client->checkout()->retrieve($sessionId, $params ?: null);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Expire a Checkout Session (cancels it before the customer completes payment).
     *
     * @throws StripeException
     */
    public function expire(string $sessionId): Session
    {
        try {
            return $this->client->checkout()->expire($sessionId);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Build a quick one-time payment session with sensible defaults.
     *
     * @param array<array<string, mixed>> $lineItems  Line items with price_data or price IDs.
     * @param string                      $successUrl
     * @param string                      $cancelUrl
     * @param array<string, mixed>        $extra       Any additional Stripe params to merge.
     * @throws StripeException
     */
    public function payment(
        array $lineItems,
        string $successUrl,
        string $cancelUrl,
        array $extra = []
    ): Session {
        return $this->createSession(array_merge([
            'mode'        => 'payment',
            'line_items'  => $lineItems,
            'success_url' => $successUrl,
            'cancel_url'  => $cancelUrl,
        ], $extra));
    }

    /**
     * Build a subscription checkout session.
     *
     * @param array<array<string, mixed>> $lineItems
     * @param array<string, mixed>        $extra
     * @throws StripeException
     */
    public function subscription(
        array $lineItems,
        string $successUrl,
        string $cancelUrl,
        array $extra = []
    ): Session {
        return $this->createSession(array_merge([
            'mode'        => 'subscription',
            'line_items'  => $lineItems,
            'success_url' => $successUrl,
            'cancel_url'  => $cancelUrl,
        ], $extra));
    }
}
