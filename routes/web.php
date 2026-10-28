<?php

use App\Http\Controllers\DocumentQaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DocumentQaController::class, 'index'])->name('home');
Route::post('/ask', [DocumentQaController::class, 'ask'])->middleware('throttle:20,1')->name('ask');
