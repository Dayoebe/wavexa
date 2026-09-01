<?php

use App\Http\Controllers\PlaybackPolicyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware('throttle:60,1')
    ->group(function (): void {
        Route::get('/health', function () {
            return response()->json([
                'status' => 'ok',
                'message' => 'Wavexa API is operational.',
                'version' => 'v1',
            ]);
        })->name('health');
        Route::get('/streams/{stream}/playback-policy', [PlaybackPolicyController::class, 'show'])->name('streams.playback-policy');
    });
