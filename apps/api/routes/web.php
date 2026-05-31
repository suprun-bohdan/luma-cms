<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Luma CMS API',
        'status' => 'pre-alpha',
    ]);
});
