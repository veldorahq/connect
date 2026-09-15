<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Veldora\Connect\Stripe\StripeClient;
use Veldora\Connect\Stripe\Services\WebhookService;
use Veldora\Connect\Stripe\Exceptions\WebhookException;

/**
 * Unit tests for WebhookService.
 *
 * Network calls are never made — we test:
 *  - constructEventUnsafe() with valid/invalid JSON
 *  - constructEvent() throws when no secret is configured
 *  - is() type comparison helper
 */
class WebhookServiceTest extends TestCase
{
    private StripeClient $client;
    private WebhookService $webhooks;

    protected function setUp(): void
    {
        $this->client = new StripeClient([
            'mode'       => 'test',
            'secret_key' => 'sk_test_webhooktest',
            'public_key' => 'pk_test_webhooktest',
            'webhook'    => [
                'secret'    => 'whsec_test_signing_secret',
                'tolerance' => 300,
            ],
        ]);

        $this->webhooks = new WebhookService($this->client);
    }

    // -------------------------------------------------------------------------
    // constructEventUnsafe
    // -------------------------------------------------------------------------

    public function test_construct_event_unsafe_accepts_array_payload(): void
    {
        $payload = [
            'id'      => 'evt_test_001',
            'object'  => 'event',
            'type'    => 'charge.succeeded',
            'created' => time(),
            'data'    => ['object' => ['id' => 'ch_test']],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => null,
        ];

        $event = $this->webhooks->constructEventUnsafe($payload);

        $this->assertSame('charge.succeeded', $event->type);
    }

    public function test_construct_event_unsafe_accepts_json_string(): void
    {
        $payload = json_encode([
            'id'               => 'evt_test_002',
            'object'           => 'event',
            'type'             => 'customer.created',
            'created'          => time(),
            'data'             => ['object' => ['id' => 'cus_test']],
            'livemode'         => false,
            'pending_webhooks' => 0,
            'request'          => null,
        ]);

        /** @var string $payload */
        $event = $this->webhooks->constructEventUnsafe($payload);

        $this->assertSame('customer.created', $event->type);
    }

    public function test_construct_event_unsafe_throws_on_invalid_json(): void
    {
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessageMatches('/JSON/i');

        $this->webhooks->constructEventUnsafe('{ invalid-json }');
    }

    // -------------------------------------------------------------------------
    // constructEvent — no secret configured
    // -------------------------------------------------------------------------

    public function test_construct_event_throws_when_no_webhook_secret(): void
    {
        $clientWithoutSecret = new StripeClient([
            'mode'       => 'test',
            'secret_key' => 'sk_test_nosecret',
            'public_key' => 'pk_test_nosecret',
            'webhook'    => ['secret' => '', 'tolerance' => 300],
        ]);

        $service = new WebhookService($clientWithoutSecret);

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessageMatches('/STRIPE_WEBHOOK_SECRET/i');

        $service->constructEvent('payload', 'sig_header');
    }

    // -------------------------------------------------------------------------
    // is() helper
    // -------------------------------------------------------------------------

    public function test_is_returns_true_for_matching_type(): void
    {
        $event = $this->webhooks->constructEventUnsafe([
            'id'               => 'evt_test_003',
            'object'           => 'event',
            'type'             => 'payment_intent.succeeded',
            'created'          => time(),
            'data'             => ['object' => []],
            'livemode'         => false,
            'pending_webhooks' => 0,
            'request'          => null,
        ]);

        $this->assertTrue($this->webhooks->is($event, 'payment_intent.succeeded'));
    }

    public function test_is_returns_false_for_non_matching_type(): void
    {
        $event = $this->webhooks->constructEventUnsafe([
            'id'               => 'evt_test_004',
            'object'           => 'event',
            'type'             => 'payment_intent.failed',
            'created'          => time(),
            'data'             => ['object' => []],
            'livemode'         => false,
            'pending_webhooks' => 0,
            'request'          => null,
        ]);

        $this->assertFalse($this->webhooks->is($event, 'payment_intent.succeeded'));
    }
}
