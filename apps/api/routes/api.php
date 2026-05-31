<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Modules\Content\Http\Controllers\Api\V1\CollectionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class);
    Route::apiResource('collections', CollectionController::class);
});
