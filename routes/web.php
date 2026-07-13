<?php
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
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

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('dashboard');

});
/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});

require __DIR__.'/auth.php';