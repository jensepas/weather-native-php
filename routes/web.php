<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use Inertia\Inertia;

// Page principale (Inertia)
Route::get('/', [ApiController::class, 'index'])->name('home');

// Page d'information (Inertia)
Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

// API
Route::prefix('api')->group(function () {
    Route::get('/search', [ApiController::class, 'search'])->name('api.search');
    Route::get('/weather', [ApiController::class, 'weather'])->name('api.weather');
    Route::post('/city', [ApiController::class, 'addCity'])->name('api.city.add');
    Route::delete('/city', [ApiController::class, 'removeCity'])->name('api.city.remove');
});
