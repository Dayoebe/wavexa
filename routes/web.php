<?php

use App\Http\Controllers\CountryController;
use App\Http\Controllers\RadioController;
use App\Http\Controllers\StreamReportController;
use App\Http\Controllers\TvController;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Pages\Home;
use App\Livewire\Pages\Tv\Index as TvIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});
Route::post('/logout', function (Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

Route::get('/radio', [RadioController::class, 'index'])->name('radio.index');
Route::get('/radio/{slug}', [RadioController::class, 'show'])->name('radio.show');
Route::post('/radio/{slug}/play', [RadioController::class, 'play'])
    ->middleware('throttle:30,1')
    ->name('radio.play');

Route::get('/tv', TvIndex::class)->name('tv.index');
Route::get('/tv/{slug}', [TvController::class, 'show'])->name('tv.show');
Route::post('/streams/{stream}/report', [StreamReportController::class, 'store'])
    ->middleware('throttle:5,10')->name('streams.report');
Route::get('/countries/{code}', [CountryController::class, 'show'])->name('countries.show');
