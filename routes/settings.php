<?php

use App\Http\Controllers\Settings\GeneralSettingsController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');

    Route::get('general', [GeneralSettingsController::class, 'edit'])->name('general.edit');
    Route::put('general', [GeneralSettingsController::class, 'update'])->name('general.update');
    Route::post('general/images/{type}', [GeneralSettingsController::class, 'storeImage'])->name('general.images.store');
    Route::delete('general/images/{type}', [GeneralSettingsController::class, 'destroyImage'])->name('general.images.destroy');
});
