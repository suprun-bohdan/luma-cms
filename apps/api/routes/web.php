<?php

use App\Modules\Forms\Http\Controllers\PublicFormSubmitController;
use App\Modules\Pages\Http\Controllers\PublicPageViewController;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use App\Modules\Seo\Http\Controllers\RobotsController;
use App\Modules\Seo\Http\Controllers\SitemapController;
use App\Modules\Setup\Services\InstallationStateService;
use Illuminate\Support\Facades\Route;

Route::get('/', function (InstallationStateService $installationState) {
    if (! $installationState->isInstalled()) {
        return redirect('/admin/setup');
    }

    $homePage = Page::query()
        ->where('slug', 'home')
        ->where('status', PageStatus::Published)
        ->first();

    if ($homePage !== null) {
        return redirect('/p/'.$homePage->slug);
    }

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
