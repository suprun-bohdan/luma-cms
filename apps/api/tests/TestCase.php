<?php

namespace Tests;

use App\Modules\Setup\Services\InstallationStateService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\SharedHostingInstallScript;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $marker = app(InstallationStateService::class)->installedMarkerPath();
        if (is_file($marker)) {
            unlink($marker);
        }

        SharedHostingInstallScript::restore();
    }
}
