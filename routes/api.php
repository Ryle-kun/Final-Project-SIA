<?php

use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\TimezoneController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Test route to check if API is working
Route::get('test', function () {
    return response()->json([
        'message' => 'API is working',
        'timestamp' => now()->toIso8601String(),
        'status' => 200
    ], 200)->header('Content-Type', 'application/json');
});

// Weather routes
Route::get('/weather/current', [WeatherController::class, 'getCurrentWeather']);
Route::get('/weather/forecast', [WeatherController::class, 'getForecast']);
Route::get('/weather/coordinates', [WeatherController::class, 'getWeatherByCoordinates']);

// Protected routes that require authentication
Route::middleware(['auth:sanctum'])->group(function () {
    // Task routes
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete']);

    // Quote favorites (requires auth)
    Route::get('/quotes/favorites', [QuoteController::class, 'getFavorites']);
    Route::post('/quotes/{quote}/favorite', [QuoteController::class, 'markAsFavorite']);
});

// Holiday routes
Route::get('/holidays/month', [HolidayController::class, 'getMonthHolidays']);
Route::get('/holidays/year', [HolidayController::class, 'getYearHolidays']);
Route::get('/holidays', [HolidayController::class, 'getHolidays']);

// Timezone routes
Route::get('/timezone/city', [TimezoneController::class, 'getTimezoneByCity']);
Route::get('/timezone/coordinates', [TimezoneController::class, 'getTimezoneByCoordinates']);
Route::get('/timezones', [TimezoneController::class, 'listTimezones']);

// Public quote routes
Route::get('/quotes/random', [QuoteController::class, 'getRandomQuote']);
Route::get('/quotes/search', [QuoteController::class, 'searchQuotes']); 