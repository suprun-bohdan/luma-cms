<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Modules\Auth\Http\Controllers\Api\V1\AuthController;
use App\Modules\Content\Http\Controllers\Api\V1\CollectionController;
use App\Modules\Content\Http\Controllers\Api\V1\EntryController;
use App\Modules\Content\Http\Controllers\Api\V1\FieldController;
use App\Modules\Content\Http\Controllers\Api\V1\PublicEntryController;
use App\Modules\Media\Http\Controllers\Api\V1\MediaController;
use App\Modules\Media\Http\Controllers\Api\V1\PublicMediaController;
use App\Modules\Pages\Http\Controllers\Api\V1\PageController;
use App\Modules\Pages\Http\Controllers\Api\V1\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class);

    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::prefix('public')->group(function (): void {
        Route::get('/collections/{collection}/entries', [PublicEntryController::class, 'index']);
        Route::get('/entries/{entry}', [PublicEntryController::class, 'show']);
        Route::get('/media/{media:uuid}', [PublicMediaController::class, 'show']);
        Route::get('/pages/{page:slug}', [PublicPageController::class, 'show']);
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

        Route::get('/media', [MediaController::class, 'index']);
        Route::post('/media', [MediaController::class, 'store']);
        Route::get('/media/{media:uuid}', [MediaController::class, 'show']);
        Route::put('/media/{media:uuid}', [MediaController::class, 'update']);
        Route::delete('/media/{media:uuid}', [MediaController::class, 'destroy']);

        Route::get('/pages', [PageController::class, 'index']);
        Route::post('/pages', [PageController::class, 'store']);
        Route::get('/pages/{page:slug}', [PageController::class, 'show']);
        Route::put('/pages/{page:slug}', [PageController::class, 'update']);
        Route::delete('/pages/{page:slug}', [PageController::class, 'destroy']);
        Route::post('/pages/{page:slug}/publish', [PageController::class, 'publish']);
        Route::post('/pages/{page:slug}/unpublish', [PageController::class, 'unpublish']);
    });
});
