<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Services;

use Stripe\Customer;
use Stripe\Collection;
use Stripe\Exception\ApiErrorException;
use Veldora\Connect\Stripe\Exceptions\StripeException;
use Veldora\Connect\Stripe\StripeClient;

/**
 * Customer management service.
 *
 * Wraps the Stripe Customers API with a clean, typed interface.
 * All Stripe API exceptions are caught and re-thrown as
 * {@see StripeException} to keep error handling consistent.
 *
 * @see https://stripe.com/docs/api/customers
 */
class CustomerService
{
    public function __construct(private readonly StripeClient $client)
    {
    }

    /**
     * Create a new Stripe customer.
     *
     * @param array<string, mixed> $attributes  Stripe customer params (email, name, metadata, etc.)
     * @throws StripeException
     */
    public function create(array $attributes): Customer
    {
        try {
            return $this->client->customers()->create($attributes);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Retrieve a Stripe customer by ID.
     *
     * @throws StripeException
     */
    public function find(string $customerId): Customer
    {
        try {
            return $this->client->customers()->retrieve($customerId);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Update an existing customer.
     *
     * @param array<string, mixed> $attributes  Fields to update.
     * @throws StripeException
     */
    public function update(string $customerId, array $attributes): Customer
    {
        try {
            return $this->client->customers()->update($customerId, $attributes);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete a customer. Returns the deleted customer object.
     *
     * @throws StripeException
     */
    public function delete(string $customerId): Customer
    {
        try {
            return $this->client->customers()->delete($customerId);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * List customers, optionally filtered.
     *
     * @param array<string, mixed> $params  Stripe list params (limit, starting_after, email, etc.)
     * @return Collection<Customer>
     * @throws StripeException
     */
    public function list(array $params = []): Collection
    {
        try {
            return $this->client->customers()->all($params);
        } catch (ApiErrorException $e) {
            throw new StripeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Find a customer by email address. Returns the first match or null.
     *
     * @throws StripeException
     */
    public function findByEmail(string $email): ?Customer
    {
        $results = $this->list(['email' => $email, 'limit' => 1]);

        /** @var Customer|null $first */
        $first = $results->data[0] ?? null;

        return $first;
    }
}
