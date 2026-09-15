<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Services;

use Stripe\Collection;
use Stripe\Subscription;
use Stripe\Exception\ApiErrorException;
use Veldora\Connect\Stripe\Exceptions\StripeException;
use Veldora\Connect\Stripe\StripeClient;

/**
 * Subscription management service.
 *
 * Wraps the Stripe Subscriptions API for recurring billing management.
 *
 * @see https://stripe.com/docs/api/subscriptions
 */
class SubscriptionService
{
    public function __construct(private readonly StripeClient $client)
    {
    }

    /**
     * Create a new subscription for a customer.
     *
     * @param string               $customerId  Stripe customer ID.
     * @param array<array<string, mixed>> $items  Array of price/quantity pairs.
     * @param array<string, mixed> $params       Additional subscription parameters.
     * @throws StripeException
     *
     * Example:
     * ```php
     * stripe()->subscriptions()->create('cus_xxx', [
     *     ['price' => 'price_yyy', 'quantity' => 1],
     * ]);
     * ```
     */
    public function create(string $customerId, array $items, array $params = []): Subscription
    {
        try {
            return $this->client->subscriptions()->create(array_merge([
                'customer' => $customerId,
                'items'    => $items,
            ], $params));
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Retrieve a subscription by ID.
     *
     * @throws StripeException
     */
    public function find(string $subscriptionId): Subscription
    {
        try {
            return $this->client->subscriptions()->retrieve($subscriptionId);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Update a subscription.
     *
     * @param array<string, mixed> $params
     * @throws StripeException
     */
    public function update(string $subscriptionId, array $params): Subscription
    {
        try {
            return $this->client->subscriptions()->update($subscriptionId, $params);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Cancel a subscription immediately or at the end of the current period.
     *
     * @param bool $atPeriodEnd  If true, the subscription remains active until period end.
     * @throws StripeException
     */
    public function cancel(string $subscriptionId, bool $atPeriodEnd = false): Subscription
    {
        try {
            if ($atPeriodEnd) {
                return $this->client->subscriptions()->update($subscriptionId, [
                    'cancel_at_period_end' => true,
                ]);
            }
            return $this->client->subscriptions()->cancel($subscriptionId);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Resume a subscription that was cancelled at period end.
     *
     * @throws StripeException
     */
    public function resume(string $subscriptionId): Subscription
    {
        return $this->update($subscriptionId, ['cancel_at_period_end' => false]);
    }

    /**
     * List subscriptions, optionally filtered.
     *
     * @param array<string, mixed> $params  Stripe list params (customer, status, limit, etc.)
     * @return Collection<Subscription>
     * @throws StripeException
     */
    public function list(array $params = []): Collection
    {
        try {
            return $this->client->subscriptions()->all($params);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * List all subscriptions for a specific customer.
     *
     * @return Collection<Subscription>
     * @throws StripeException
     */
    public function forCustomer(string $customerId, array $params = []): Collection
    {
        return $this->list(array_merge(['customer' => $customerId], $params));
    }
}
