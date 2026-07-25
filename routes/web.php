<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\RiskScoreController;
use App\Http\Controllers\ShippingRouteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\WorldBankController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

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

Route::resource('countries', CountryController::class)->only(['index', 'show']);
Route::get('/watchlists', [WatchlistController::class, 'index'])->name('watchlists.index');
Route::post('/watchlists/{country}', [WatchlistController::class, 'store'])->name('watchlists.store');
Route::delete('/watchlists/{watchlist}', [WatchlistController::class, 'destroy'])->name('watchlists.destroy');

/*
|--------------------------------------------------------------------------
| Ports
|--------------------------------------------------------------------------
*/

Route::resource('ports', PortController::class)->only(['index', 'show']);

/*
|--------------------------------------------------------------------------
| Shipping Routes
|--------------------------------------------------------------------------
*/

Route::get(
    '/shipping-routes',
    [ShippingRouteController::class, 'index']
)->name('shipping-routes.index');

/*
|--------------------------------------------------------------------------
| Weather
|--------------------------------------------------------------------------
*/

Route::get('/weather', [WeatherController::class, 'index'])
    ->name('weather.index');

Route::get('/weather-map', [WeatherController::class, 'map'])
    ->name('weather.map');

Route::get('/weather/{city}', [WeatherController::class, 'show'])
    ->name('weather.show');

/*
|--------------------------------------------------------------------------
| Exchange Rate
|--------------------------------------------------------------------------
*/

Route::get('/exchange-rate', [ExchangeRateController::class, 'index'])
    ->name('exchange.index');

/*
|--------------------------------------------------------------------------
| Economy
|--------------------------------------------------------------------------
*/

Route::get('/economy', [WorldBankController::class, 'index'])
    ->name('economy.index');

Route::get('/worldbank/{country}', [WorldBankController::class, 'show'])
    ->name('worldbank.show');

/*
|--------------------------------------------------------------------------
| News
|--------------------------------------------------------------------------
*/

Route::get('/news', [NewsController::class, 'index'])
    ->name('news.index');

/*
|--------------------------------------------------------------------------
| Risk Score
|--------------------------------------------------------------------------
*/

Route::get('/risk-score', [RiskScoreController::class, 'index'])
    ->name('risk.index');

/*
|--------------------------------------------------------------------------
| Map
|--------------------------------------------------------------------------
*/

Route::get('/map', [MapController::class, 'index'])
    ->name('map.index');

/*
|--------------------------------------------------------------------------
| Country Comparison
|--------------------------------------------------------------------------
*/

Route::get(
    '/comparison',
    [ComparisonController::class, 'index']
)->name('comparison.index');

Route::post(
    '/comparison',
    [ComparisonController::class, 'compare']
)->name('comparison.compare');

/*
|--------------------------------------------------------------------------
| Admin Dashboard
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'can:admin'])
    ->group(function () {

        Route::post('/countries/import-api', [CountryController::class, 'importApi'])->name('countries.importApi');
        Route::resource('countries', CountryController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

        Route::get('/ports', [AdminDashboardController::class, 'ports'])->name('ports.index');
        Route::resource('ports', PortController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::resource('articles', ArticleController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('/settings', [AdminDashboardController::class, 'settings'])
            ->name('settings');

    });
