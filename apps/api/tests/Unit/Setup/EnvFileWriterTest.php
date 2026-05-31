<?php

declare(strict_types=1);

namespace Tests\Unit\Setup;

use App\Modules\Setup\Services\EnvFileWriter;
use Tests\TestCase;

final class EnvFileWriterTest extends TestCase
{
    public function test_merge_updates_and_appends_env_values(): void
    {
        $path = base_path('.env.testing');
        file_put_contents($path, "APP_NAME=Laravel\nDB_CONNECTION=sqlite\n");

        $writer = new EnvFileWriter();
        $writer->merge($path, [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'APP_URL' => 'http://localhost',
        ]);

        $contents = file_get_contents($path);

        $this->assertStringContainsString('DB_CONNECTION=mysql', $contents);
        $this->assertStringContainsString('DB_HOST=127.0.0.1', $contents);
        $this->assertStringNotContainsString('APP_URL=', $contents);
        $this->assertStringContainsString('APP_NAME=Laravel', $contents);
        $this->assertFileExists(storage_path('framework/env-writer.bak'));

        @unlink($path);
        @unlink(storage_path('framework/env-writer.bak'));
        @unlink(storage_path('framework/env-writer.lock'));
    }

    public function test_merge_ignores_keys_outside_allowlist(): void
    {
        $path = base_path('.env.testing-allowlist');
        file_put_contents($path, "APP_KEY=secret\n");

        $writer = new EnvFileWriter();
        $writer->merge($path, [
            'APP_KEY' => 'hacked',
            'APP_NAME' => 'Safe Site',
        ]);

        $contents = file_get_contents($path);

        $this->assertStringContainsString('APP_KEY=secret', $contents);
        $this->assertStringContainsString('APP_NAME="Safe Site"', $contents);

        @unlink($path);
        @unlink(storage_path('framework/env-writer.bak'));
        @unlink(storage_path('framework/env-writer.lock'));
    }
}
