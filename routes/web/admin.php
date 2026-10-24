<?php

/*
|--------------------------------------------------------------------------
| Route Administrasi
|--------------------------------------------------------------------------
|
| Role & permission, activity log, pengguna, tim, dan modul pengguna.
| Controller: app/Http/Controllers/Admin.
| Dimuat di dalam grup auth + verified.phone (routes/web.php).
|
*/

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ModulController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TimAssignmentController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Role & permission
Route::middleware(['can:role.manage'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('permissions', PermissionController::class)->only(['index', 'create', 'store', 'destroy']);
});

// Activity log
Route::middleware(['can:activity-log.view'])->prefix('activity-log')->name('activity-log.')->group(function () {
    Route::get('/', [ActivityLogController::class, 'index'])->name('index');
});

// Ekspor pengguna — didaftarkan sebelum resource `users` agar /users/export tidak dianggap /users/{user}
Route::middleware(['can:user.export'])->group(function () {
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
});

// Pengguna
Route::middleware(['can:user.manage'])->group(function () {
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
});

// Penugasan tim (verifikator & fasilitator)
Route::middleware(['can:tim-assignment.manage'])->prefix('tim-assignment')->name('tim-assignment.')->controller(TimAssignmentController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('/{timAssignment}/edit', 'edit')->name('edit');
    Route::put('/{timAssignment}', 'update')->name('update');
    Route::post('/{timAssignment}/toggle-status', 'toggleStatus')->name('toggle-status');
    Route::post('/toggle-tim-status', 'toggleTimStatus')->name('toggle-tim-status');
    Route::get('/{timAssignment}/download-sk', 'downloadSk')->name('download-sk');
});

// Modul pengguna: semua pengguna login dapat melihat & mengunduh (dibatasi role modul di controller)
Route::prefix('modul')->name('modul.')->controller(ModulController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{modul}/download', 'download')->name('download');

    Route::middleware(['can:modul.manage'])->group(function () {
        Route::post('/', 'store')->name('store');
        Route::delete('/{modul}', 'destroy')->name('destroy');
    });
});
