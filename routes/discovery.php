<?php

use App\Http\Controllers\DiscoveryController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [DiscoveryController::class, 'robots']);
Route::get('/sitemap.xml', [DiscoveryController::class, 'sitemap']);
Route::get('/sitemaps/pages.xml', [DiscoveryController::class, 'pages']);
Route::get('/sitemaps/countries.xml', [DiscoveryController::class, 'countries']);
Route::get('/sitemaps/radio.xml', [DiscoveryController::class, 'radio']);
Route::get('/sitemaps/tv.xml', [DiscoveryController::class, 'tv']);
Route::get('/llms.txt', [DiscoveryController::class, 'llms']);
Route::get('/llms-full.txt', [DiscoveryController::class, 'llmsFull']);
Route::get('/ai.txt', [DiscoveryController::class, 'ai']);
Route::get('/feed.xml', [DiscoveryController::class, 'feed']);
