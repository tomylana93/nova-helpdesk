<?php

use App\Http\Controllers\Helpdesk\TicketApprovalController;
use App\Http\Controllers\Helpdesk\TicketCommentController;
use App\Http\Controllers\Helpdesk\TicketController;
use App\Http\Controllers\Helpdesk\TicketLifecycleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->group(function () {
    Route::resource('tickets', TicketController::class)->only([
        'index', 'create', 'store', 'show', 'edit', 'update',
    ]);

    Route::post('tickets/{ticket}/comments', [TicketCommentController::class, 'store'])
        ->name('tickets.comments.store');

    Route::post('tickets/{ticket}/sync-assets', [TicketController::class, 'syncAssets'])
        ->name('tickets.sync-assets');

    Route::post('tickets/{ticket}/approve', [TicketApprovalController::class, 'approve'])
        ->name('tickets.approve');
    Route::post('tickets/{ticket}/reject', [TicketApprovalController::class, 'reject'])
        ->name('tickets.reject');

    Route::post('tickets/{ticket}/transition', [TicketLifecycleController::class, 'transition'])
        ->name('tickets.transition');

    Route::post('tickets/{ticket}/reopen', [TicketLifecycleController::class, 'reopen'])
        ->name('tickets.reopen');
    Route::post('tickets/{ticket}/confirm-resolved', [TicketLifecycleController::class, 'confirm'])
        ->name('tickets.confirm-resolved');
});
