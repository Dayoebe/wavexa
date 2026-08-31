<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Radio\DataQuality as RadioDataQuality;
use App\Livewire\Admin\Radio\Duplicates as RadioDuplicates;
use App\Livewire\Admin\Radio\Form as RadioForm;
use App\Livewire\Admin\Radio\Index as RadioIndex;
use App\Livewire\Admin\Radio\Show as RadioShow;
use App\Livewire\Admin\StreamHealth;
use Illuminate\Support\Facades\Route;

Route::get('/', Dashboard::class)->name('dashboard');
Route::get('/stream-health', StreamHealth::class)->name('stream-health');
Route::get('/radio', RadioIndex::class)->name('radio.index');
Route::get('/radio/create', RadioForm::class)->name('radio.create');
Route::get('/radio/duplicates', RadioDuplicates::class)->name('radio.duplicates');
Route::get('/radio/data-quality', RadioDataQuality::class)->name('radio.data-quality');
Route::get('/radio/{station}/edit', RadioForm::class)->name('radio.edit');
Route::get('/radio/{station}', RadioShow::class)->name('radio.show');
