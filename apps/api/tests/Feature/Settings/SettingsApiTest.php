<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class SettingsApiTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_reads_and_updates_settings(): void
    {
        $this->getJson('/api/v1/settings', $this->withBearer($this->adminUser()))
            ->assertOk()
            ->assertJsonStructure(['data' => ['site', 'seo']]);

        $this->patchJson(
            '/api/v1/settings',
            [
                'site.title' => 'Acme Corp',
                'site.tagline' => 'We build things',
            ],
            $this->withBearer($this->adminUser()),
        )
            ->assertOk()
            ->assertJsonPath('data.site.title', 'Acme Corp');
    }
}
