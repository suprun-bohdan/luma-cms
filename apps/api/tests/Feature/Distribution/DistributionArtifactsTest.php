<?php

declare(strict_types=1);

namespace Tests\Feature\Distribution;

use Tests\TestCase;

final class DistributionArtifactsTest extends TestCase
{
    public function test_shared_hosting_artifacts_exist(): void
    {
        $root = dirname(base_path(), 2);

        $this->assertFileExists($root.'/INSTALL.txt');
        $this->assertFileExists($root.'/apps/api/.env.shared.example');
        $this->assertFileExists($root.'/deploy/nginx/luma.conf');
        $this->assertFileExists($root.'/deploy/apache/luma.htaccess');
        // docs/ is gitignored; deployment guides live in the outer workspace only.
    }
}
