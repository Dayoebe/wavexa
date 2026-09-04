<?php

use App\Http\Controllers\UserLibraryController;
use App\Livewire\Pages\Library;
use Illuminate\Support\Facades\Route;

Route::get('/library', Library::class)->name('library');
Route::post('/library/favorites/{media}', [UserLibraryController::class, 'toggleFavorite'])->name('library.favorites.toggle');
Route::post('/library/history/{media}', [UserLibraryController::class, 'recordPlayback'])->name('library.history.record');
Route::post('/library/history/stream/{stream}', [UserLibraryController::class, 'recordStreamPlayback'])->name('library.history.stream');
