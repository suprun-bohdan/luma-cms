<?php

declare(strict_types=1);

namespace App\Modules\Setup\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

final class EnvFileWriter
{
    /** @var list<string> */
    private const ALLOWED_KEYS = [
        'APP_NAME',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
    ];

    /**
     * @param  array<string, scalar|null>  $values
     */
    public function merge(string $path, array $values): void
    {
        $values = array_intersect_key(
            $values,
            array_flip(self::ALLOWED_KEYS),
        );

        if ($values === []) {
            return;
        }

        $sidecarDir = $this->writableSidecarDir($path);
        $lockPath = $sidecarDir.'/env-writer.lock';
        $lockHandle = fopen($lockPath, 'c+');

        if ($lockHandle === false) {
            throw new RuntimeException('Unable to open environment file lock.');
        }

        try {
            if (! flock($lockHandle, LOCK_EX)) {
                throw new RuntimeException('Unable to acquire environment file lock.');
            }

            $lines = is_file($path) ? file($path, FILE_IGNORE_NEW_LINES) : [];

            foreach ($lines as $index => $line) {
                if (! str_contains($line, '=') || str_starts_with(ltrim($line), '#')) {
                    continue;
                }

                [$key] = explode('=', $line, 2);

                if (! array_key_exists($key, $values)) {
                    continue;
                }

                $lines[$index] = $this->formatLine($key, $values[$key]);
                unset($values[$key]);
            }

            foreach ($values as $key => $value) {
                $lines[] = $this->formatLine($key, $value);
            }

            if (is_file($path)) {
                File::copy($path, $sidecarDir.'/env-writer.bak');
            }

            $contents = implode(PHP_EOL, $lines).PHP_EOL;

            if (is_file($path) && ! is_writable($path)) {
                throw new RuntimeException('Environment file is not writable.');
            }

            if (file_put_contents($path, $contents, LOCK_EX) === false) {
                throw new RuntimeException('Unable to write environment file.');
            }
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    /**
     * Shared hosting often allows updating an existing .env (0666) but not creating
     * new files next to it — use storage/framework which is world-writable in releases.
     */
    private function writableSidecarDir(string $envPath): string
    {
        $dir = dirname($envPath).'/storage/framework';

        if (! is_dir($dir)) {
            throw new RuntimeException('Storage framework directory is missing.');
        }

        if (! is_writable($dir)) {
            throw new RuntimeException('Storage framework directory is not writable.');
        }

        return $dir;
    }

    private function formatLine(string $key, mixed $value): string
    {
        if ($value === null) {
            return $key.'=';
        }

        if (is_bool($value)) {
            return $key.'='.($value ? 'true' : 'false');
        }

        $string = (string) $value;

        if ($string === '' || preg_match('/[\s#="\']/u', $string)) {
            return $key.'="'.addcslashes($string, '"\\').'"';
        }

        return $key.'='.$string;
    }
}
