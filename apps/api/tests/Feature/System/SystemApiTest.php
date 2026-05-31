<?php

declare(strict_types=1);

namespace Tests\Feature\System;

use App\Core\Update\UpdateService;
use App\Modules\Setup\Models\LumaInstallation;
use App\Modules\Users\Models\Role;
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
            ->assertJsonPath('data.version', '0.0.25-rc.10')
            ->assertJsonPath('data.installed', false);
    }

    public function test_owner_runs_web_update_after_install(): void
    {
        LumaInstallation::query()->create([
            'completed_at' => now(),
            'version' => '0.0.23-dev',
        ]);

        $this->postJson('/api/v1/system/update/run', [], $this->withBearer($this->ownerUser()))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure(['data' => ['migrations']]);

        $this->assertDatabaseHas('setup_logs', [
            'step' => 'system.update',
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'system.update.started',
            'subject_type' => 'system',
            'subject_id' => 'luma',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'system.update.succeeded',
            'subject_type' => 'system',
            'subject_id' => 'luma',
        ]);
    }

    public function test_admin_without_owner_permission_cannot_run_update(): void
    {
        LumaInstallation::query()->create([
            'completed_at' => now(),
            'version' => '0.0.23-dev',
        ]);

        $this->postJson('/api/v1/system/update/run', [], $this->withBearer($this->adminUser()))
            ->assertForbidden();
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
            'version' => '0.0.26-dev',
            'flavor' => 'shared',
            'build' => '20260531',
        ], JSON_THROW_ON_ERROR));

        try {
            $this->getJson('/api/v1/system/update/check', $this->withBearer($this->adminUser()))
                ->assertOk()
                ->assertJsonPath('data.update_available', true)
                ->assertJsonPath('data.latest', '0.0.26-dev');
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
        $this->postJson('/api/v1/system/update/run', [], $this->withBearer($this->ownerUser()))
            ->assertStatus(422)
            ->assertJsonPath('ok', false);
    }

    public function test_editor_cannot_check_or_run_updates(): void
    {
        LumaInstallation::query()->create([
            'completed_at' => now(),
            'version' => '0.0.24-dev',
        ]);

        $headers = $this->withBearer($this->editorUser());

        $this->getJson('/api/v1/system/update/check', $headers)->assertForbidden();
        $this->postJson('/api/v1/system/update/run', [], $headers)->assertForbidden();
    }

    public function test_admin_role_excludes_owner_only_update_permissions(): void
    {
        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
        $slugs = $adminRole->permissions()->pluck('slug')->all();

        $this->assertNotContains('system.update.run', $slugs);
        $this->assertNotContains('setup.view_logs', $slugs);
        $this->assertContains('system.update.check', $slugs);
    }

    public function test_update_failure_writes_audit_log(): void
    {
        LumaInstallation::query()->create([
            'completed_at' => now(),
            'version' => '0.0.24-dev',
        ]);

        $this->mock(UpdateService::class, function ($mock): void {
            $mock->shouldReceive('run')
                ->once()
                ->andThrow(new \RuntimeException('Simulated migration failure'));
        });

        $this->postJson('/api/v1/system/update/run', [], $this->withBearer($this->ownerUser()))
            ->assertStatus(422)
            ->assertJsonPath('ok', false);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'system.update.started',
            'subject_type' => 'system',
            'subject_id' => 'luma',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'system.update.failed',
            'subject_type' => 'system',
            'subject_id' => 'luma',
        ]);

        $this->assertDatabaseHas('setup_logs', [
            'step' => 'system.update',
            'status' => 'error',
        ]);
    }
}
