<?php

use Illuminate\Support\Facades\Route;

// Catch all routes and serve the Vue.js application
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
