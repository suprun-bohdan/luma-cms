<?php

use App\Modules\Pages\Http\Controllers\PublicPageViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Luma CMS API',
        'status' => 'pre-alpha',
    ]);
});

Route::get('/p/{page:slug}', [PublicPageViewController::class, 'show']);
