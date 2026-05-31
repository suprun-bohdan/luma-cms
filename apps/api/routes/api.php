<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Modules\Auth\Http\Controllers\Api\V1\AuthController;
use App\Modules\Content\Http\Controllers\Api\V1\CollectionController;
use App\Modules\Content\Http\Controllers\Api\V1\EntryController;
use App\Modules\Content\Http\Controllers\Api\V1\FieldController;
use App\Modules\Content\Http\Controllers\Api\V1\PublicEntryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class);

    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::prefix('public')->group(function (): void {
        Route::get('/collections/{collection}/entries', [PublicEntryController::class, 'index']);
        Route::get('/entries/{entry}', [PublicEntryController::class, 'show']);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::apiResource('collections', CollectionController::class);
        Route::apiResource('collections.fields', FieldController::class)
            ->scoped([
                'collection' => 'slug',
                'field' => 'slug',
            ]);

        Route::get('/collections/{collection}/entries', [EntryController::class, 'index']);
        Route::post('/collections/{collection}/entries', [EntryController::class, 'store']);
        Route::get('/entries/{entry}', [EntryController::class, 'show']);
        Route::put('/entries/{entry}', [EntryController::class, 'update']);
        Route::delete('/entries/{entry}', [EntryController::class, 'destroy']);
        Route::post('/entries/{entry}/publish', [EntryController::class, 'publish']);
        Route::post('/entries/{entry}/unpublish', [EntryController::class, 'unpublish']);
    });
});
