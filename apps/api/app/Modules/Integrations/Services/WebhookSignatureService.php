<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

final class WebhookSignatureService
{
    public function sign(string $secret, int $timestamp, string $body): string
    {
        $payload = $timestamp.'.'.$body;

        return hash_hmac('sha256', $payload, $secret);
    }

    /** @return array<string, string> */
    public function headers(string $secret, string $body): array
    {
        $timestamp = time();
        $signature = $this->sign($secret, $timestamp, $body);

        return [
            'X-Luma-Signature' => 't='.$timestamp.',v1='.$signature,
            'Content-Type' => 'application/json',
            'User-Agent' => 'Luma-CMS-Webhook/1.0',
        ];
    }

    public function verify(string $secret, int $timestamp, string $body, string $signature, int $toleranceSeconds = 300): bool
    {
        if (abs(time() - $timestamp) > $toleranceSeconds) {
            return false;
        }

        $expected = $this->sign($secret, $timestamp, $body);

        return hash_equals($expected, $signature);
    }
}
