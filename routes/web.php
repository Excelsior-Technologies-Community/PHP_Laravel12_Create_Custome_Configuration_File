<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;

Route::get('helper', function () {
    $googleAPIToken = config('google.api_token');

    dd($googleAPIToken);
});

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin/settings')
    ->name('settings.')
    ->group(function () {

        Route::get('/', [
            SettingsController::class,
            'index'
        ])->name('index');

        Route::get('/create', [
            SettingsController::class,
            'create'
        ])->name('create');

        Route::post('/', [
            SettingsController::class,
            'store'
        ])->name('store');

        /*
         * IMPORTANT:
         * These routes must be before /{setting}/edit
         * so Laravel doesn't treat "export" and "import"
         * as setting IDs.
         */
        Route::get('/export', [
            SettingsController::class,
            'export'
        ])->name('export');

        Route::post('/import', [
            SettingsController::class,
            'import'
        ])->name('import');

        Route::get('/{setting}/edit', [
            SettingsController::class,
            'edit'
        ])->name('edit');

        Route::put('/{setting}', [
            SettingsController::class,
            'update'
        ])->name('update');

        Route::delete('/{setting}', [
            SettingsController::class,
            'destroy'
        ])->name('destroy');

        Route::get('/{setting}/history', [
            SettingsController::class,
            'history'
        ])->name('history');

        Route::post('/{setting}/rollback/{history}', [
            SettingsController::class,
            'rollback'
        ])->name('rollback');

        Route::post('/bulk-update', [
            SettingsController::class,
            'bulkUpdate'
        ])->name('bulkUpdate');

        Route::post('/env-update', [
            SettingsController::class,
            'updateEnv'
        ])->name('envUpdate');

        Route::post('/switch-environment', [
            SettingsController::class,
            'switchEnvironment'
        ])->name('switchEnvironment');
    });