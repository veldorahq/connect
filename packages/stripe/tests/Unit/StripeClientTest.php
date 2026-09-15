<?php

declare(strict_types=1);

namespace Veldora\Connect\Stripe\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Veldora\Connect\Stripe\StripeClient;
use Veldora\Connect\Stripe\Exceptions\InvalidConfigException;

/**
 * Unit tests for StripeClient configuration validation and construction.
 *
 * These tests do NOT make network calls — they verify that the client
 * validates its config correctly and exposes the right accessors.
 */
class StripeClientTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    /**
     * Return a minimal valid test-mode config.
     *
     * @return array<string, mixed>
     */
    private function testConfig(): array
    {
        return [
            'mode'       => 'test',
            'secret_key' => 'sk_test_123abc',
            'public_key' => 'pk_test_456def',
            'currency'   => 'usd',
            'webhook'    => [
                'secret'    => 'whsec_test',
                'tolerance' => 300,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Construction / happy path
    // -------------------------------------------------------------------------

    public function test_constructs_successfully_with_valid_test_config(): void
    {
        $client = new StripeClient($this->testConfig());

        $this->assertSame('pk_test_456def', $client->publicKey());
        $this->assertSame('test', $client->mode());
        $this->assertSame('usd', $client->defaultCurrency());
    }

    public function test_constructs_successfully_with_valid_live_config(): void
    {
        $config = $this->testConfig();
        $config['mode']       = 'live';
        $config['secret_key'] = 'sk_live_realkey';
        $config['public_key'] = 'pk_live_realkey';

        $client = new StripeClient($config);

        $this->assertSame('live', $client->mode());
    }

    public function test_mode_defaults_to_test_when_not_provided(): void
    {
        $config = $this->testConfig();
        unset($config['mode']);

        $client = new StripeClient($config);

        $this->assertSame('test', $client->mode());
    }

    public function test_currency_defaults_to_usd_when_not_provided(): void
    {
        $config = $this->testConfig();
        unset($config['currency']);

        $client = new StripeClient($config);

        $this->assertSame('usd', $client->defaultCurrency());
    }

    // -------------------------------------------------------------------------
    // Config validation — missing keys
    // -------------------------------------------------------------------------

    public function test_throws_if_secret_key_is_missing(): void
    {
        $config = $this->testConfig();
        unset($config['secret_key']);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageMatches('/secret_key/i');

        new StripeClient($config);
    }

    public function test_throws_if_secret_key_is_empty_string(): void
    {
        $config = $this->testConfig();
        $config['secret_key'] = '';

        $this->expectException(InvalidConfigException::class);

        new StripeClient($config);
    }

    // -------------------------------------------------------------------------
    // Config validation — key prefix / mode mismatch
    // -------------------------------------------------------------------------

    public function test_throws_if_test_mode_has_live_secret_key(): void
    {
        $config = $this->testConfig();
        $config['mode']       = 'test';
        $config['secret_key'] = 'sk_live_abcdef';

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageMatches('/mode/i');

        new StripeClient($config);
    }

    public function test_throws_if_live_mode_has_test_secret_key(): void
    {
        $config = $this->testConfig();
        $config['mode']       = 'live';
        $config['secret_key'] = 'sk_test_abcdef';

        $this->expectException(InvalidConfigException::class);

        new StripeClient($config);
    }

    public function test_throws_if_mode_is_invalid(): void
    {
        $config = $this->testConfig();
        $config['mode'] = 'staging';

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessageMatches('/mode/i');

        new StripeClient($config);
    }

    // -------------------------------------------------------------------------
    // webhookConfig accessor
    // -------------------------------------------------------------------------

    public function test_webhook_config_returns_secret_and_tolerance(): void
    {
        $client = new StripeClient($this->testConfig());

        $wc = $client->webhookConfig();

        $this->assertSame('whsec_test', $wc['secret']);
        $this->assertSame(300, $wc['tolerance']);
    }

    public function test_webhook_config_defaults_when_not_configured(): void
    {
        $config = $this->testConfig();
        unset($config['webhook']);

        $client = new StripeClient($config);

        $wc = $client->webhookConfig();

        $this->assertSame('', $wc['secret']);
        $this->assertSame(300, $wc['tolerance']);
    }

    // -------------------------------------------------------------------------
    // getClient (raw SDK)
    // -------------------------------------------------------------------------

    public function test_get_client_returns_stripe_sdk_instance(): void
    {
        $client = new StripeClient($this->testConfig());

        $this->assertInstanceOf(\Stripe\StripeClient::class, $client->getClient());
    }
}
