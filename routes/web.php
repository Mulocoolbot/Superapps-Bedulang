<?php

use App\Http\Controllers\Admin\CCTVController;
use App\Http\Controllers\Admin\ComplaintController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BloodStockController;
use App\Http\Controllers\InformationServiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PimpinanDashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\KubernetesController;
/*
|--------------------------------------------------------------------------
| Kubernetes
|--------------------------------------------------------------------------
*/
Route::get('/healthz', [KubernetesController::class, 'sehat']);
Route::get('/readiness', [KubernetesController::class, 'siap']);

/*
|--------------------------------------------------------------------------
| Front Portal
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Front/Home');
});

Route::post('/complaints', [ComplaintController::class, 'store'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('complaints.store');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('/cctv', [CCTVController::class, 'index'])
        ->name('admin.cctv');

    // Complaint routes
    Route::get('/complaints', [ComplaintController::class, 'index'])
        ->name('admin.complaints.index');

    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])
        ->name('admin.complaints.show');

    Route::put('/complaints/{complaint}', [ComplaintController::class, 'update'])
        ->name('admin.complaints.update');

    Route::delete('/complaints/{complaint}', [ComplaintController::class, 'destroy'])
        ->name('admin.complaints.destroy');

    Route::get('/blood-stocks/history', [BloodStockController::class, 'history'])
        ->name('admin.blood-stocks.history');

    Route::post('/blood-stocks', [BloodStockController::class, 'update'])
        ->name('admin.blood-stocks.update');
});

Route::get('/utd/history', [BloodStockController::class, 'history'])
    ->name('utd.history');

Route::middleware(['auth', 'super_admin'])->prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index'])
        ->name('admin.users');

    Route::get('/users/create', [UserController::class, 'create'])
        ->name('admin.users.create');

    Route::post('/users', [UserController::class, 'store'])
        ->name('admin.users.store');

    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->name('admin.users.edit');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->name('admin.users.update');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->name('admin.users.destroy');
});

/*
|--------------------------------------------------------------------------
| Profile & User Complaints
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/layanan/{slug}', [InformationServiceController::class, 'show'])
        ->name('layanan.show');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/pimpinan', [PimpinanDashboardController::class, 'index'])
        ->name('pimpinan.dashboard');

    Route::get('/my-complaints', [\App\Http\Controllers\UserComplaintController::class, 'index'])
        ->name('user.complaints.index');

    Route::patch('/my-complaints/{complaint}/cancel', [\App\Http\Controllers\UserComplaintController::class, 'cancel'])
        ->name('user.complaints.cancel');

    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});

require __DIR__.'/auth.php';

