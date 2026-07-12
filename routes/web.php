<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\WorldBankController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\RiskScoreController;

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Countries
|--------------------------------------------------------------------------
*/

Route::get('/countries/import', [CountryController::class, 'import'])
    ->name('countries.import');

Route::post('/countries/import', [CountryController::class, 'importStore'])
    ->name('countries.import.store');

Route::get(
    '/countries/import-api',
    [CountryController::class, 'importApi']
)->name('countries.import.api');

Route::resource('countries', CountryController::class);

Route::get('/risk-score', [RiskScoreController::class, 'index'])
    ->name('risk.index');
/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
*/

Route::get('/weather/{city}', [WeatherController::class, 'show'])
    ->name('weather.show');

Route::get('/exchange-rate', [ExchangeRateController::class, 'index'])
    ->name('exchange.index');

Route::get('/worldbank/{country}', [WorldBankController::class, 'show'])
    ->name('worldbank.show');

Route::get('/news', [NewsController::class, 'index'])
    ->name('news.index');