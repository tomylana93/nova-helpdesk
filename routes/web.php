<?php

use App\Http\Controllers\TemporaryUploadController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return to_route('login');
    }

    return to_route('dashboard');
})->name('home');

Route::middleware(['auth', 'active'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::post('temporary-uploads', [TemporaryUploadController::class, 'store'])
        ->middleware('throttle:temporary-uploads')
        ->name('temporary-uploads.store');
    Route::delete('temporary-uploads/{temporaryUpload}', [TemporaryUploadController::class, 'destroy'])->name('temporary-uploads.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
