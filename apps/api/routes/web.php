<?php

use App\Modules\Forms\Http\Controllers\Api\V1\FormController;
use App\Modules\Forms\Http\Controllers\PublicFormSubmitController;
use App\Modules\Pages\Http\Controllers\PublicPageViewController;
use App\Modules\Seo\Http\Controllers\RobotsController;
use App\Modules\Seo\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Luma CMS API',
        'status' => 'pre-alpha',
    ]);
});

Route::get('/sitemap.xml', SitemapController::class);
Route::get('/robots.txt', RobotsController::class);
Route::post('/public/forms/{form:slug}/submit', PublicFormSubmitController::class)
    ->middleware('throttle:10,1');
Route::get('/p/{page:slug}', [PublicPageViewController::class, 'show']);
