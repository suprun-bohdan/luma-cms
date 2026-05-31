<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Requirements;

use App\Core\Requirements\EnvironmentRequirementChecker;
use App\Core\Requirements\PhpVersionEvaluator;
use App\Core\Requirements\RequirementStatus;
use App\Core\Requirements\VendorAutoloadEvaluator;
use Tests\TestCase;

final class EnvironmentRequirementCheckerTest extends TestCase
{
    public function test_default_checker_includes_php_version_and_vendor(): void
    {
        $report = EnvironmentRequirementChecker::default()->check();

        $ids = array_map(static fn ($check) => $check->id, $report->checks);

        $this->assertContains('php.version', $ids);
        $this->assertContains('php.extensions', $ids);
        $this->assertContains('paths.writable', $ids);
        $this->assertContains('vendor.autoload', $ids);
        $this->assertTrue($report->passed());
    }

    public function test_checker_fails_when_php_is_too_old(): void
    {
        $checker = new EnvironmentRequirementChecker([
            new PhpVersionEvaluator('7.4.0'),
            new VendorAutoloadEvaluator(),
        ]);

        $report = $checker->check();

        $this->assertFalse($report->passed());
        $this->assertSame(
            RequirementStatus::Failed,
            $report->blockingFailures()[0]->status,
        );
    }
}
