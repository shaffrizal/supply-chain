<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GlobalApiController;
use Illuminate\Http\Request;

Route::get('/countries', [GlobalApiController::class, 'getCountries']);
Route::get('/risk', [GlobalApiController::class, 'getRisk']);
Route::get('/ports', [GlobalApiController::class, 'getPorts']);
Route::get('/news', [GlobalApiController::class, 'getNews']);
Route::get('/currency', [GlobalApiController::class, 'getCurrency']);
Route::get('/overview', [GlobalApiController::class, 'getOverview'])->name('api.overview');
