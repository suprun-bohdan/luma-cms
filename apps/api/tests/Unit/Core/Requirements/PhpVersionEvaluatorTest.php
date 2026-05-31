<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Requirements;

use App\Core\Requirements\PhpVersionEvaluator;
use App\Core\Requirements\RequirementStatus;
use Tests\TestCase;

final class PhpVersionEvaluatorTest extends TestCase
{
    public function test_php_7_4_is_blocked(): void
    {
        $check = (new PhpVersionEvaluator('7.4.33'))->evaluate();

        $this->assertSame(RequirementStatus::Failed, $check->status);
        $this->assertStringContainsString('8.3.0', $check->message);
    }

    public function test_php_8_2_is_blocked(): void
    {
        $check = (new PhpVersionEvaluator('8.2.29'))->evaluate();

        $this->assertSame(RequirementStatus::Failed, $check->status);
    }

    public function test_php_8_3_is_supported_legacy_branch(): void
    {
        $check = (new PhpVersionEvaluator('8.3.12'))->evaluate();

        $this->assertNotSame(RequirementStatus::Failed, $check->status);
        $this->assertSame(RequirementStatus::Warning, $check->status);
    }

    public function test_php_8_4_passes(): void
    {
        $check = (new PhpVersionEvaluator('8.4.6'))->evaluate();

        $this->assertSame(RequirementStatus::Passed, $check->status);
    }

    public function test_php_8_5_warns_as_untested_branch(): void
    {
        $check = (new PhpVersionEvaluator('8.5.0'))->evaluate();

        $this->assertSame(RequirementStatus::Warning, $check->status);
        $this->assertStringContainsString('8.5', $check->message);
    }

    public function test_php_8_3_1_warns_below_recommended(): void
    {
        config([
            'luma.php.minimum' => '8.3.0',
            'luma.php.recommended' => '8.4.0',
            'luma.php.supported_branches' => ['8.3', '8.4'],
        ]);

        $check = (new PhpVersionEvaluator('8.3.1'))->evaluate();

        $this->assertSame(RequirementStatus::Warning, $check->status);
        $this->assertStringContainsString('Recommended upgrade', $check->message);
    }
}
