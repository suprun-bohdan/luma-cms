<?php

declare(strict_types=1);

namespace Tests\Unit\Integrations;

use App\Modules\Integrations\Jobs\DeliverWebhookJob;
use Tests\TestCase;

final class DeliverWebhookJobTest extends TestCase
{
    public function test_job_uses_queue_native_retry_contract(): void
    {
        $job = new DeliverWebhookJob('00000000-0000-0000-0000-000000000001');

        $this->assertSame(4, $job->tries);
        $this->assertSame([30, 120, 600], $job->backoff);
    }
}
