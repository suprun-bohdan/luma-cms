<?php

declare(strict_types=1);

namespace Tests\Feature\System;

use App\Modules\Setup\Models\LumaInstallation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateLumaCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_command_fails_when_not_installed(): void
    {
        $this->artisan('luma:update', ['--force' => true])
            ->assertFailed();
    }

    public function test_update_command_runs_when_installed(): void
    {
        LumaInstallation::query()->create([
            'completed_at' => now(),
            'version' => '0.0.23-dev',
        ]);

        $this->artisan('luma:update', ['--force' => true])
            ->assertSuccessful();
    }
}
