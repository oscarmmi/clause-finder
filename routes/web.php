<?php

use App\Http\Controllers\ClauseFinderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ClauseFinderController::class, 'index'])->name('home');
Route::post('/agreements', [ClauseFinderController::class, 'store'])->name('agreements.store');
Route::post('/search', [ClauseFinderController::class, 'search'])->name('search');
