<?php

declare(strict_types=1);

namespace Tests\Feature\Onboarding;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class OnboardingSkipEnvTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_skip_config_marks_onboarding_completed(): void
    {
        config(['luma.skip_onboarding' => true]);

        $this->getJson('/api/v1/onboarding/progress', $this->withBearer($this->adminUser()))
            ->assertOk()
            ->assertJsonPath('data.completed', true);
    }
}
