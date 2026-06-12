<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Reports\ReportController;
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
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export/operational', [ReportController::class, 'exportOperational'])->name('reports.export.operational');
    Route::get('reports/export/audit', [ReportController::class, 'exportAudit'])->name('reports.export.audit');

    Route::post('temporary-uploads', [TemporaryUploadController::class, 'store'])
        ->middleware('throttle:temporary-uploads')
        ->name('temporary-uploads.store');
    Route::delete('temporary-uploads/{temporaryUpload}', [TemporaryUploadController::class, 'destroy'])->name('temporary-uploads.destroy');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/helpdesk.php';
