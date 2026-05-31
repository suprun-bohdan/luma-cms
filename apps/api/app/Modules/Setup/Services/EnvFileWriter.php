<?php

declare(strict_types=1);

namespace App\Modules\Setup\Services;

final class EnvFileWriter
{
    /**
     * @param  array<string, scalar|null>  $values
     */
    public function merge(string $path, array $values): void
    {
        $lines = is_file($path) ? file($path, FILE_IGNORE_NEW_LINES) : [];
        $updatedKeys = [];

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
            $updatedKeys[] = $key;
        }

        foreach ($values as $key => $value) {
            $lines[] = $this->formatLine($key, $value);
        }

        if (is_file($path)) {
            copy($path, $path.'.bak');
        }

        file_put_contents($path, implode(PHP_EOL, $lines).PHP_EOL);
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
