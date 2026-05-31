<?php

declare(strict_types=1);

namespace App\Modules\Setup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SetupDatabaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'driver' => ['required', 'string', Rule::in(['sqlite', 'mysql', 'mariadb', 'pgsql'])],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'database' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, mixed> */
    public function databaseConfig(): array
    {
        $driver = $this->string('driver')->toString();

        if ($driver === 'sqlite') {
            return [
                'driver' => 'sqlite',
                'database' => $this->input('database') ?: database_path('database.sqlite'),
            ];
        }

        return [
            'driver' => $driver === 'mariadb' ? 'mysql' : $driver,
            'host' => $this->input('host'),
            'port' => $this->input('port'),
            'database' => $this->input('database'),
            'username' => $this->input('username'),
            'password' => $this->input('password'),
        ];
    }
}
