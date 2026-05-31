<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Install\InstallOptions;
use App\Core\Install\InstallService;
use App\Core\Requirements\RequirementStatus;
use Illuminate\Console\Command;

class InstallLumaCommand extends Command
{
    protected $signature = 'luma:install
                            {--force : Run non-interactively without confirmation}
                            {--admin-email= : Admin user email}
                            {--admin-password= : Admin user password}
                            {--with-starter-site : Install demo starter site content}';

    protected $description = 'Install Luma CMS: prerequisites, migrate, seed, admin user, storage link';

    public function handle(InstallService $installService): int
    {
        if (! $this->option('force') && ! $this->confirm('Install Luma CMS?', true)) {
            $this->components->warn('Installation cancelled.');

            return self::SUCCESS;
        }

        $report = $installService->requirements();
        foreach ($report->checks as $check) {
            match ($check->status) {
                RequirementStatus::Passed => $this->components->info("[OK] {$check->label}: {$check->message}"),
                RequirementStatus::Warning => $this->components->warn("[WARN] {$check->label}: {$check->message}"),
                RequirementStatus::Failed => $this->components->error("[FAIL] {$check->label}: {$check->message}"),
            };
        }

        if (! $report->passed()) {
            $this->components->error('Installation blocked until all required checks pass.');

            return self::FAILURE;
        }

        try {
            $installService->install(new InstallOptions(
                adminEmail: $this->option('admin-email'),
                adminPassword: $this->option('admin-password'),
                withStarterSite: (bool) $this->option('with-starter-site'),
            ));
        } catch (\Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->success('Luma CMS installed successfully.');

        return self::SUCCESS;
    }
}
