<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

final class SystemRequirementsTest extends TestCase
{
    public function test_system_requirements_endpoint_returns_checks(): void
    {
        $response = $this->getJson('/api/v1/system/requirements');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'passed',
                'checks' => [
                    ['id', 'label', 'status', 'message'],
                ],
            ])
            ->assertJsonPath('passed', true);

        $ids = collect($response->json('checks'))->pluck('id')->all();

        $this->assertContains('php.version', $ids);
        $this->assertContains('vendor.autoload', $ids);
    }
}
