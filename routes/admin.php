<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Radio\DataQuality as RadioDataQuality;
use App\Livewire\Admin\Radio\Duplicates as RadioDuplicates;
use App\Livewire\Admin\Radio\Form as RadioForm;
use App\Livewire\Admin\Radio\Index as RadioIndex;
use App\Livewire\Admin\Radio\Show as RadioShow;
use App\Livewire\Admin\StreamHealth;
use App\Livewire\Admin\Television\Form as TelevisionForm;
use App\Livewire\Admin\Television\Index as TelevisionIndex;
use App\Livewire\Admin\Television\Show as TelevisionShow;
use Illuminate\Support\Facades\Route;

Route::get('/', Dashboard::class)->name('dashboard');
Route::get('/stream-health', StreamHealth::class)->name('stream-health');
Route::get('/radio', RadioIndex::class)->name('radio.index');
Route::get('/radio/create', RadioForm::class)->name('radio.create');
Route::get('/radio/duplicates', RadioDuplicates::class)->name('radio.duplicates');
Route::get('/radio/data-quality', RadioDataQuality::class)->name('radio.data-quality');
Route::get('/radio/{station}/edit', RadioForm::class)->name('radio.edit');
Route::get('/radio/{station}', RadioShow::class)->name('radio.show');
Route::get('/television', TelevisionIndex::class)->name('television.index');
Route::get('/television/create', TelevisionForm::class)->name('television.create');
Route::get('/television/{channel}/edit', TelevisionForm::class)->name('television.edit');
Route::get('/television/{channel}', TelevisionShow::class)->name('television.show');
