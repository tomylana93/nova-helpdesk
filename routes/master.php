<?php

use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\MasterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('master')->name('master.')->group(function () {
    Route::get('/', [MasterController::class, 'index'])->name('index');
    Route::resource('users', UserController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->whereUuid('user');
});
