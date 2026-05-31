<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Models\User;
use App\Modules\Pages\Services\StarterSiteService;
use App\Modules\Settings\Services\SettingsService;
use App\Modules\Setup\Services\SetupLogService;
use InvalidArgumentException;

final class OnboardingService
{
    /** @var array<string, string> */
    private const STEPS = [
        'welcome' => 'Welcome',
        'site_type' => 'Site type',
        'starter_content' => 'Starter content',
        'integrations' => 'Integrations',
        'done' => 'Done',
    ];

    public function __construct(
        private readonly SettingsService $settings,
        private readonly SetupLogService $setupLog,
        private readonly StarterSiteService $starterSite,
    ) {}

    public function isCompleted(): bool
    {
        if (filter_var(config('luma.skip_onboarding'), FILTER_VALIDATE_BOOL)) {
            return true;
        }

        return $this->settings->get('onboarding.completed_at') !== null;
    }

    /**
     * @return array{
     *     completed: bool,
     *     percent: int,
     *     steps: list<array{id: string, label: string, status: string, completed_at: string|null}>
     * }
     */
    public function progress(): array
    {
        $steps = [];
        $completedCount = 0;
        $foundCurrent = false;

        foreach (self::STEPS as $id => $label) {
            $completedAt = $this->stepCompletedAt($id);
            $status = 'pending';

            if ($completedAt !== null) {
                $status = 'completed';
                $completedCount++;
            } elseif (! $foundCurrent && ! $this->isCompleted()) {
                $status = 'current';
                $foundCurrent = true;
            }

            $steps[] = [
                'id' => $id,
                'label' => $label,
                'status' => $status,
                'completed_at' => $completedAt,
            ];
        }

        $total = count(self::STEPS);

        return [
            'completed' => $this->isCompleted(),
            'percent' => $this->isCompleted() ? 100 : (int) round(($completedCount / $total) * 100),
            'steps' => $steps,
        ];
    }

    public function completeWelcome(string $siteTitle, string $industry): void
    {
        $this->settings->set('site.title', $siteTitle);
        $this->settings->set('onboarding.industry', $industry, 'onboarding');
        $this->markStepComplete('welcome');
        $this->setupLog->write(
            'onboarding.welcome',
            'success',
            'Site profile saved during onboarding.',
            ['industry' => $industry],
        );
    }

    public function completeSiteType(string $preset): void
    {
        if (! in_array($preset, ['business', 'blog', 'portfolio'], true)) {
            throw new InvalidArgumentException("Unknown site preset: {$preset}");
        }

        $this->settings->set('onboarding.preset', $preset, 'onboarding');
        $this->markStepComplete('site_type');
        $this->setupLog->write(
            'onboarding.site_type',
            'success',
            "Selected starter preset: {$preset}.",
        );
    }

    public function installStarterContent(User $admin): void
    {
        $preset = (string) ($this->settings->get('onboarding.preset') ?: 'business');
        $page = $this->starterSite->installPreset($preset, $admin);

        $this->markStepComplete('starter_content');
        $this->setupLog->write(
            'onboarding.starter_content',
            'success',
            "Starter content installed ({$preset}).",
            ['page_slug' => $page->slug],
        );
    }

    public function skipIntegrations(): void
    {
        $this->settings->set('onboarding.integrations_skipped', true, 'onboarding');
        $this->markStepComplete('integrations');
        $this->setupLog->write(
            'onboarding.integrations',
            'skipped',
            'Integrations step skipped.',
        );
    }

    public function finish(): void
    {
        $this->markStepComplete('done');
        $this->settings->set('onboarding.completed_at', now()->toIso8601String(), 'onboarding');
        $this->setupLog->write(
            'onboarding.finish',
            'success',
            'Onboarding completed.',
        );
    }

    public function markFullyCompletedFromInstall(): void
    {
        foreach (array_keys(self::STEPS) as $step) {
            if ($this->stepCompletedAt($step) === null) {
                $this->markStepComplete($step);
            }
        }

        if (! $this->isCompleted()) {
            $this->settings->set('onboarding.completed_at', now()->toIso8601String(), 'onboarding');
        }
    }

    private function markStepComplete(string $step): void
    {
        if (! isset(self::STEPS[$step])) {
            throw new InvalidArgumentException("Unknown onboarding step: {$step}");
        }

        $this->settings->set(
            "onboarding.step.{$step}.completed_at",
            now()->toIso8601String(),
            'onboarding',
        );
    }

    private function stepCompletedAt(string $step): ?string
    {
        $value = $this->settings->get("onboarding.step.{$step}.completed_at");

        return is_string($value) && $value !== '' ? $value : null;
    }
}
