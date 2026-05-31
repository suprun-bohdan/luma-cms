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

        $lockPath = $path.'.lock';
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
                File::copy($path, $path.'.bak');
            }

            $contents = implode(PHP_EOL, $lines).PHP_EOL;
            $temporaryPath = $path.'.tmp.'.getmypid();

            if (file_put_contents($temporaryPath, $contents, LOCK_EX) === false) {
                throw new RuntimeException('Unable to write temporary environment file.');
            }

            if (! rename($temporaryPath, $path)) {
                @unlink($temporaryPath);

                throw new RuntimeException('Unable to replace environment file.');
            }
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
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
