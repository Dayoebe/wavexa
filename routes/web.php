<?php

use App\Http\Controllers\CountryController;
use App\Http\Controllers\RadioController;
use App\Http\Controllers\StreamReportController;
use App\Http\Controllers\TvController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/radio', [RadioController::class, 'index'])->name('radio.index');
Route::get('/radio/{slug}', [RadioController::class, 'show'])->name('radio.show');
Route::post('/radio/{slug}/play', [RadioController::class, 'play'])
    ->middleware('throttle:30,1')
    ->name('radio.play');

Route::get('/tv', [TvController::class, 'index'])->name('tv.index');
Route::get('/tv/{slug}', [TvController::class, 'show'])->name('tv.show');
Route::post('/streams/{stream}/report', [StreamReportController::class, 'store'])
    ->middleware('throttle:5,10')->name('streams.report');
Route::get('/countries/{code}', [CountryController::class, 'show'])->name('countries.show');
