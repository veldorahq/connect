<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Services;

use Stripe\Event;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Veldora\Connect\Stripe\Exceptions\WebhookException;
use Veldora\Connect\Stripe\StripeClient;

/**
 * Webhook verification and event construction service.
 *
 * Validates incoming Stripe webhook payloads using the webhook signing
 * secret, which prevents accepting spoofed webhook events.
 *
 * Usage in a controller:
 * ```php
 * $payload = file_get_contents('php://input');
 * $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
 *
 * try {
 *     $event = stripe()->webhooks()->constructEvent($payload, $signature);
 * } catch (WebhookException $e) {
 *     // Respond 400 — signature invalid or event too old
 *     return response('Webhook Error: ' . $e->getMessage(), 400);
 * }
 *
 * match ($event->type) {
 *     'checkout.session.completed' => $this->handleCheckoutComplete($event->data->object),
 *     'customer.subscription.deleted' => $this->handleSubscriptionCancelled($event->data->object),
 *     default => null,
 * };
 * ```
 *
 * @see https://stripe.com/docs/webhooks/signatures
 */
class WebhookService
{
    /**
     * The webhook signing secret (whsec_...).
     */
    private readonly string $secret;

    /**
     * Maximum age (seconds) of a webhook event before it is rejected.
     */
    private readonly int $tolerance;

    public function __construct(private readonly StripeClient $client)
    {
        // These are set on the StripeClient config — pull via reflection-free accessor.
        // We access them via the raw client config array returned from the Manager.
        // WebhookService expects the client to expose a webhookConfig() helper.
        $webhookConfig = $this->client->webhookConfig();

        $this->secret    = (string) ($webhookConfig['secret'] ?? '');
        $this->tolerance = (int) ($webhookConfig['tolerance'] ?? 300);
    }

    /**
     * Construct a verified Stripe Event from the raw payload and signature header.
     *
     * @param string $payload    Raw request body (use `file_get_contents('php://input')`).
     * @param string $sigHeader  Value of the `Stripe-Signature` HTTP header.
     *
     * @throws WebhookException if signature verification fails or event is too old.
     */
    public function constructEvent(string $payload, string $sigHeader): Event
    {
        if (empty($this->secret)) {
            throw new WebhookException(
                'STRIPE_WEBHOOK_SECRET is not set. '
                . 'Add it to your .env file (whsec_...) from your Stripe Dashboard webhook settings.'
            );
        }

        try {
            return Webhook::constructEvent($payload, $sigHeader, $this->secret, $this->tolerance);
        } catch (SignatureVerificationException $e) {
            throw new WebhookException(
                'Webhook signature verification failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Construct an event without signature verification.
     *
     * ⚠ ONLY use in automated tests or when you are 100% certain
     * the payload comes from a trusted source. Never use in production.
     *
     * @param array<string, mixed>|string $payload  JSON string or decoded array.
     * @throws WebhookException if JSON is invalid.
     */
    public function constructEventUnsafe(array|string $payload): Event
    {
        $json = is_array($payload) ? json_encode($payload) : $payload;

        if ($json === false) {
            throw new WebhookException('Failed to encode webhook payload as JSON.');
        }

        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new WebhookException('Invalid webhook payload — could not decode JSON.');
        }

        return Event::constructFrom($data);
    }

    /**
     * Check whether a given Stripe event type matches.
     *
     * Convenience helper to avoid inline string comparison.
     *
     * ```php
     * if ($this->webhooks()->is($event, 'checkout.session.completed')) { ... }
     * ```
     */
    public function is(Event $event, string $type): bool
    {
        return $event->type === $type;
    }
}
