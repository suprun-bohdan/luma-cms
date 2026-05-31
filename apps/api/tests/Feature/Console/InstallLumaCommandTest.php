<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class InstallLumaCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_command_runs_successfully(): void
    {
        $this->artisan('luma:install', [
            '--force' => true,
            '--admin-email' => 'installer@luma.test',
            '--admin-password' => 'secret-password',
        ])
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'installer@luma.test',
        ]);

        $admin = User::query()->where('email', 'installer@luma.test')->firstOrFail();
        $this->assertTrue($admin->roles()->where('slug', 'owner')->exists());
    }
}
