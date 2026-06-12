<?php

use App\Http\Controllers\Admin\MasterData\AssetController;
use App\Http\Controllers\Admin\MasterData\BranchController;
use App\Http\Controllers\Admin\MasterData\DepartmentController;
use App\Http\Controllers\Admin\MasterData\SlaPolicyController;
use App\Http\Controllers\Admin\MasterData\TicketCategoryController;
use App\Http\Controllers\Admin\MasterData\UserController;
use App\Http\Controllers\Admin\Settings\GeneralSettingsController;
use App\Http\Controllers\Admin\Settings\PasswordSettingsController;
use App\Http\Controllers\Admin\Settings\StyleSettingsController;
use App\Models\User;
use App\Settings\GeneralSettings;
use App\Settings\PasswordSettings;
use App\Settings\StyleSettings;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::inertia('/', 'admin/settings/Index')->can('view', GeneralSettings::class)->name('index');
        Route::get('general', [GeneralSettingsController::class, 'edit'])->can('view', GeneralSettings::class)->name('general.edit');
        Route::patch('general', [GeneralSettingsController::class, 'update'])->can('update', GeneralSettings::class)->name('general.update');
        Route::get('style', [StyleSettingsController::class, 'edit'])->can('view', StyleSettings::class)->name('style.edit');
        Route::patch('style', [StyleSettingsController::class, 'update'])->can('update', StyleSettings::class)->name('style.update');
        Route::get('password', [PasswordSettingsController::class, 'edit'])
            ->middleware(RequirePassword::class)
            ->can('view', PasswordSettings::class)
            ->name('password.edit');
        Route::patch('password', [PasswordSettingsController::class, 'update'])
            ->middleware(RequirePassword::class)
            ->can('update', PasswordSettings::class)
            ->name('password.update');
    });

    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::inertia('/', 'admin/master-data/Index')->can('viewAny', User::class)->name('index');
        Route::resource('users', UserController::class)->only([
            'index', 'create', 'store', 'show', 'edit', 'update',
        ]);
        Route::resource('branches', BranchController::class)->only([
            'index', 'create', 'store', 'show', 'edit', 'update',
        ]);
        Route::resource('departments', DepartmentController::class)->only([
            'index', 'create', 'store', 'show', 'edit', 'update',
        ]);
        Route::resource('ticket-categories', TicketCategoryController::class)->only([
            'index', 'create', 'store', 'show', 'edit', 'update',
        ]);
        Route::resource('sla-policies', SlaPolicyController::class)->only([
            'index', 'create', 'store', 'show', 'edit', 'update',
        ]);
        Route::resource('assets', AssetController::class);
    });
});
