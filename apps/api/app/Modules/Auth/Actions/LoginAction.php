<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Models\User;
use App\Modules\Auth\Data\LoginResult;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class LoginAction
{
    /**
     * @param  array{email: string, password: string}  $credentials
     */
    public function execute(array $credentials): LoginResult
    {
        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('luma-api')->plainTextToken;

        return new LoginResult($token, $user->load('roles'));
    }
}
