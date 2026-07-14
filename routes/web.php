<?php
use App\Http\Controllers\Admin\CCTVController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\InformationServiceController;
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

Route::get('/layanan/{slug}', [InformationServiceController::class, 'show'])
    ->name('layanan.show');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/cctv', [CCTVController::class, 'index'])
        ->name('admin.cctv');

    Route::middleware(['super_admin'])->group(function () {
        Route::get('/admin/users', [UserController::class, 'index'])
            ->name('admin.users');

        Route::get('/admin/users/create', [UserController::class, 'create'])
            ->name('admin.users.create');

        Route::post('/admin/users', [UserController::class, 'store'])
            ->name('admin.users.store');

        Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])
            ->name('admin.users.edit');

        Route::put('/admin/users/{user}', [UserController::class, 'update'])
            ->name('admin.users.update');

        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])
            ->name('admin.users.destroy');
    });

});
/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});


require __DIR__.'/auth.php';