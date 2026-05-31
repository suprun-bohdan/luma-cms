<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Update\UpdateService;
use App\Modules\Setup\Services\InstallationStateService;
use Illuminate\Console\Command;

class UpdateLumaCommand extends Command
{
    protected $signature = 'luma:update {--force : Run without confirmation}';

    protected $description = 'Apply database migrations and clear caches after a release upgrade';

    public function handle(InstallationStateService $installationState, UpdateService $updateService): int
    {
        if (! $installationState->isInstalled()) {
            $this->components->error('Luma CMS is not installed yet. Run luma:install first.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Run Luma update steps?', true)) {
            return self::SUCCESS;
        }

        $this->components->info('Running migrations...');
        $result = $updateService->run();
        $this->line($result['migrations']);

        $this->components->success('Update completed. Restart queue workers if running.');

        return self::SUCCESS;
    }
}
