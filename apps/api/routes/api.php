<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Modules\Auth\Http\Controllers\Api\V1\AuthController;
use App\Modules\Content\Http\Controllers\Api\V1\CollectionController;
use App\Modules\Content\Http\Controllers\Api\V1\FieldController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class);

    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::apiResource('collections', CollectionController::class);
        Route::apiResource('collections.fields', FieldController::class)
            ->scoped([
                'collection' => 'slug',
                'field' => 'slug',
            ]);
    });
});
