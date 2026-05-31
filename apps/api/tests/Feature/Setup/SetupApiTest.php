<?php

declare(strict_types=1);

namespace Tests\Feature\Setup;

use App\Modules\Setup\Models\LumaInstallation;
use App\Modules\Setup\Services\InstallationStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SetupApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_status_is_not_installed_initially(): void
    {
        $this->getJson('/api/v1/setup/status')
            ->assertOk()
            ->assertJsonPath('installed', false);
    }

    public function test_setup_finish_installs_application(): void
    {
        $this->postJson('/api/v1/setup/finish', [
            'site_title' => 'Test Site',
            'admin_email' => 'setup@luma.test',
            'admin_password' => 'secret-pass',
            'with_starter_site' => true,
        ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertTrue(app(InstallationStateService::class)->isInstalled());
        $this->assertDatabaseHas('users', ['email' => 'setup@luma.test']);
        $this->assertDatabaseHas('pages', ['slug' => 'home']);
    }

    public function test_setup_routes_are_locked_after_install(): void
    {
        LumaInstallation::query()->create([
            'completed_at' => now(),
            'version' => '0.0.22-dev',
        ]);

        $this->getJson('/api/v1/setup/requirements')->assertForbidden();
    }
}
