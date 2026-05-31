<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Actions\LoginAction;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Resources\AuthUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return response()->json([
            'data' => [
                'token' => $result->token,
                'token_type' => 'Bearer',
                'user' => AuthUserResource::make($result->user)->resolve(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(null, 204);
    }

    public function me(Request $request): AuthUserResource
    {
        $user = $request->user();
        $user?->load('roles');

        return AuthUserResource::make($user);
    }
}
