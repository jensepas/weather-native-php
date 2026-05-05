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

// Page des paramètres
Route::get('/settings', function () {
    return Inertia::render('Settings');
})->name('settings');

// Page de gestion des villes
Route::get('/cities', [ApiController::class, 'cities'])->name('cities.manage');

// API
Route::prefix('api')->group(function () {
    Route::get('/search', [ApiController::class, 'search'])->name('api.search');
    Route::get('/weather', [ApiController::class, 'weather'])->name('api.weather');
    Route::post('/city', [ApiController::class, 'addCity'])->name('api.city.add');
    Route::delete('/city', [ApiController::class, 'removeCity'])->name('api.city.remove');
    Route::get('/cities-list', [ApiController::class, 'citiesList'])->name('api.cities.list');

    Route::get('/cities/export', [ApiController::class, 'exportCities'])->name('api.cities.export');
    Route::post('/cities/import', [ApiController::class, 'importCities'])->name('api.cities.import');
});
