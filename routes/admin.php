<?php

use App\Http\Controllers\Admin\StreamHealthController;
use Illuminate\Support\Facades\Route;

Route::get('/stream-health', StreamHealthController::class)->name('stream-health');
