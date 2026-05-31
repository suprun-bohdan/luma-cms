<?php

declare(strict_types=1);

namespace Tests\Unit\Integrations;

use App\Modules\Integrations\Services\WebhookSignatureService;
use Tests\TestCase;

final class WebhookSignatureServiceTest extends TestCase
{
    public function test_sign_and_verify_round_trip(): void
    {
        $service = new WebhookSignatureService();
        $secret = 'test-secret';
        $timestamp = time();
        $body = '{"event":"page.published"}';

        $signature = $service->sign($secret, $timestamp, $body);

        $this->assertTrue($service->verify($secret, $timestamp, $body, $signature));
    }

    public function test_verify_rejects_tampered_body(): void
    {
        $service = new WebhookSignatureService();
        $timestamp = time();
        $body = '{"event":"page.published"}';
        $signature = $service->sign('secret', $timestamp, $body);

        $this->assertFalse($service->verify('secret', $timestamp, '{"event":"tampered"}', $signature));
    }
}
