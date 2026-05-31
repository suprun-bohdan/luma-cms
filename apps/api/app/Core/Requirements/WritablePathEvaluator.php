<?php

declare(strict_types=1);

namespace App\Core\Requirements;

use App\Core\Requirements\Contract\RequirementEvaluator;
use Illuminate\Support\Facades\File;

final class WritablePathEvaluator implements RequirementEvaluator
{
    public function evaluate(): RequirementCheck
    {
        /** @var list<string> $relativePaths */
        $relativePaths = config('luma.paths.writable', []);
        $notWritable = [];

        foreach ($relativePaths as $relativePath) {
            $path = base_path($relativePath);

            if (! is_dir($path)) {
                File::ensureDirectoryExists($path);
            }

            if (! is_writable($path)) {
                $notWritable[] = $relativePath;
            }
        }

        if ($notWritable !== []) {
            return new RequirementCheck(
                id: 'paths.writable',
                label: 'Writable directories',
                status: RequirementStatus::Failed,
                message: 'These paths must be writable: '.implode(', ', $notWritable),
            );
        }

        return new RequirementCheck(
            id: 'paths.writable',
            label: 'Writable directories',
            status: RequirementStatus::Passed,
            message: 'Storage, bootstrap cache, and database directory are writable.',
        );
    }
}
