<?php

declare(strict_types=1);

namespace Tests\Feature\Onboarding;

use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class OnboardingApiTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_completes_onboarding_flow(): void
    {
        $this->getJson('/api/v1/onboarding/progress', $this->withBearer($this->adminUser()))
            ->assertOk()
            ->assertJsonPath('data.completed', false)
            ->assertJsonPath('data.steps.0.id', 'welcome');

        $this->postJson(
            '/api/v1/onboarding/welcome',
            [
                'site_title' => 'Acme Studio',
                'industry' => 'technology',
            ],
            $this->withBearer($this->adminUser()),
        )
            ->assertOk()
            ->assertJsonPath('data.steps.0.status', 'completed');

        $this->postJson(
            '/api/v1/onboarding/site-type',
            ['preset' => 'business'],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        $this->postJson('/api/v1/onboarding/starter', [], $this->withBearer($this->adminUser()))
            ->assertOk();

        $this->assertDatabaseHas('pages', ['slug' => 'home']);

        $this->postJson('/api/v1/onboarding/integrations', [], $this->withBearer($this->adminUser()))
            ->assertOk();

        $this->postJson('/api/v1/onboarding/finish', [], $this->withBearer($this->adminUser()))
            ->assertOk()
            ->assertJsonPath('data.completed', true)
            ->assertJsonPath('data.percent', 100);

        $this->getJson('/api/v1/settings', $this->withBearer($this->adminUser()))
            ->assertOk()
            ->assertJsonPath('data.site.title', 'Acme Studio');

        $this->assertNotNull(Page::query()->where('slug', 'home')->first());
    }

    public function test_onboarding_journal_lists_recent_events(): void
    {
        $this->postJson(
            '/api/v1/onboarding/welcome',
            [
                'site_title' => 'Journal Site',
                'industry' => 'retail',
            ],
            $this->withBearer($this->ownerUser()),
        )->assertOk();

        $this->getJson('/api/v1/onboarding/journal', $this->withBearer($this->ownerUser()))
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'step', 'status', 'message']]]);
    }

    public function test_admin_cannot_view_setup_journal_without_permission(): void
    {
        $this->getJson('/api/v1/onboarding/journal', $this->withBearer($this->adminUser()))
            ->assertForbidden();
    }
}
