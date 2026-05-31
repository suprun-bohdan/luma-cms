<?php

declare(strict_types=1);

namespace Tests\Feature\System;

use App\Modules\Setup\Models\LumaInstallation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class SystemApiTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_version_endpoint_is_public(): void
    {
        $this->getJson('/api/v1/system/version')
            ->assertOk()
            ->assertJsonStructure(['data' => ['version', 'installed']])
            ->assertJsonPath('data.installed', false);
    }

    public function test_admin_runs_web_update_after_install(): void
    {
        LumaInstallation::query()->create([
            'completed_at' => now(),
            'version' => '0.0.23-dev',
        ]);

        $this->postJson('/api/v1/system/update/run', [], $this->withBearer($this->adminUser()))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure(['data' => ['migrations']]);

        $this->assertDatabaseHas('setup_logs', [
            'step' => 'system.update',
            'status' => 'success',
        ]);
    }

    public function test_update_check_uses_manifest_version_when_present(): void
    {
        LumaInstallation::query()->create([
            'completed_at' => now(),
            'version' => '0.0.23-dev',
        ]);

        $manifestPath = dirname(base_path(), 2).'/luma-manifest.json';
        $backup = File::exists($manifestPath) ? File::get($manifestPath) : null;

        File::put($manifestPath, json_encode([
            'product' => 'luma-cms',
            'version' => '0.0.24-dev',
            'flavor' => 'shared',
            'build' => '20260531',
        ], JSON_THROW_ON_ERROR));

        try {
            $this->getJson('/api/v1/system/update/check', $this->withBearer($this->adminUser()))
                ->assertOk()
                ->assertJsonPath('data.update_available', true)
                ->assertJsonPath('data.latest', '0.0.24-dev');
        } finally {
            if ($backup === null) {
                File::delete($manifestPath);
            } else {
                File::put($manifestPath, $backup);
            }
        }
    }

    public function test_web_update_requires_installation(): void
    {
        $this->postJson('/api/v1/system/update/run', [], $this->withBearer($this->adminUser()))
            ->assertStatus(422)
            ->assertJsonPath('ok', false);
    }
}
