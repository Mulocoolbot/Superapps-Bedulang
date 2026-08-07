<?php

use App\Http\Controllers\Admin\CCTVController;
use App\Http\Controllers\Admin\ComplaintController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BloodStockController;
use App\Http\Controllers\InformationServiceController;
use App\Http\Controllers\UserComplaintController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PimpinanDashboardController;
use App\Http\Controllers\Admin\KoperasiImportController;
use App\Http\Controllers\Admin\KependudukanImportController;
use App\Http\Controllers\KoperasiPimpinanController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\KubernetesController;
use App\Http\Controllers\DataSektoralController;
use App\Http\Controllers\AppLaunchController;

/*
|--------------------------------------------------------------------------
| Kubernetes & App Health Check Launch
|--------------------------------------------------------------------------
*/
Route::get('/healthz', [KubernetesController::class, 'sehat']);
Route::get('/readiness', [KubernetesController::class, 'siap']);

Route::get('/app/launch', [AppLaunchController::class, 'launch'])->name('app.launch');
Route::get('/app/maintenance', [AppLaunchController::class, 'showMaintenance'])->name('app.maintenance');
Route::get('/api/check-app-status', [AppLaunchController::class, 'checkStatus'])->name('api.check-app-status');

/*
|--------------------------------------------------------------------------
| Front Portal
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Front/Home');
});

Route::get('/cctv', [CCTVController::class, 'index'])->name('cctv.index');
Route::get('/cctv-stream/{base64_url}/{file}', [\App\Http\Controllers\CctvProxyController::class, 'proxy'])
    ->where('file', '.*')
    ->name('cctv.stream');

Route::post('/complaints', [ComplaintController::class, 'store'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('complaints.store');

Route::get('/download-app', function () {
    $path = public_path('downloads/Bedulang.apk');
    if (file_exists($path)) {
        return response()->download($path, 'Bedulang.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
    return redirect()->back()->with('warning', 'File Bedulang.apk tidak ditemukan.');
})->name('download.app');

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
    // KDMP Admin Keseluruhan
    Route::get('/admin/kdmp', [\App\Http\Controllers\Kdmp\AdminKdmpController::class, 'dashboard'])->name('admin.kdmp.dashboard');
    Route::post('/admin/kdmp/admin', [\App\Http\Controllers\Kdmp\AdminKdmpController::class, 'storeAdmin'])->name('admin.kdmp.store_admin');
    Route::put('/admin/kdmp/admin/{user}', [\App\Http\Controllers\Kdmp\AdminKdmpController::class, 'updateAdmin'])->name('admin.kdmp.update_admin');
    Route::delete('/admin/kdmp/admin/{user}', [\App\Http\Controllers\Kdmp\AdminKdmpController::class, 'destroyAdmin'])->name('admin.kdmp.destroy_admin');
    Route::post('/admin/kdmp/admin/{user}/verify', [\App\Http\Controllers\Kdmp\AdminKdmpController::class, 'verifyAdminKmp'])->name('admin.kdmp.verify_admin');
    Route::get('/admin/kdmp/history', [\App\Http\Controllers\Kdmp\AdminKdmpController::class, 'historyLog'])->name('admin.kdmp.history');

    // KDMP Data Import
    Route::get('/admin/kdmp/import', [\App\Http\Controllers\Kdmp\KmpImportController::class, 'index'])->name('admin.kdmp.import_kmp');
    Route::post('/admin/kdmp/import', [\App\Http\Controllers\Kdmp\KmpImportController::class, 'import'])->name('admin.kdmp.import_kmp.store');
    Route::get('/admin/kdmp/import/template', [\App\Http\Controllers\Kdmp\KmpImportController::class, 'downloadTemplate'])->name('admin.kdmp.import_kmp.template');
    Route::put('/admin/kdmp/import/{kmp}', [\App\Http\Controllers\Kdmp\KmpImportController::class, 'update'])->name('admin.kdmp.import_kmp.update');
    Route::delete('/admin/kdmp/import/{kmp}', [\App\Http\Controllers\Kdmp\KmpImportController::class, 'destroy'])->name('admin.kdmp.import_kmp.destroy');
    Route::delete('/admin/kdmp/import/log/{log}', [\App\Http\Controllers\Kdmp\KmpImportController::class, 'destroyLog'])->name('admin.kdmp.import_kmp.destroy_log');

    // KDMP Admin Kecamatan
    Route::get('/admin/kdmp/kecamatan', [\App\Http\Controllers\Kdmp\AdminKecamatanController::class, 'dashboard'])->name('admin.kdmp.kecamatan.dashboard');
    Route::get('/admin/kdmp/kecamatan/verifikasi-kmp', [\App\Http\Controllers\Kdmp\AdminKecamatanController::class, 'verifikasiKmp'])->name('admin.kdmp.kecamatan.verifikasi_kmp_page');
    Route::get('/admin/kdmp/kecamatan/verifikasi-perubahan', [\App\Http\Controllers\Kdmp\AdminKecamatanController::class, 'verifikasiPerubahan'])->name('admin.kdmp.kecamatan.verifikasi_perubahan_page');
    Route::post('/admin/kdmp/kecamatan/admin-kmp', [\App\Http\Controllers\Kdmp\AdminKecamatanController::class, 'storeKmpAdmin'])->name('admin.kdmp.kecamatan.store_admin');
    Route::put('/admin/kdmp/kecamatan/admin-kmp/{user}', [\App\Http\Controllers\Kdmp\AdminKecamatanController::class, 'updateAdmin'])->name('admin.kdmp.kecamatan.update_admin');
    Route::delete('/admin/kdmp/kecamatan/admin-kmp/{user}', [\App\Http\Controllers\Kdmp\AdminKecamatanController::class, 'destroyAdmin'])->name('admin.kdmp.kecamatan.destroy_admin');
    Route::post('/admin/kdmp/kecamatan/kmp/{kmp}/verify-pembentukan', [\App\Http\Controllers\Kdmp\AdminKecamatanController::class, 'verifyPembentukan'])->name('admin.kdmp.kecamatan.verify_pembentukan');
    Route::post('/admin/kdmp/kecamatan/kmp/{kmp}/verify-sertifikat', [\App\Http\Controllers\Kdmp\AdminKecamatanController::class, 'verifySertifikat'])->name('admin.kdmp.kecamatan.verify_sertifikat');
    Route::post('/admin/kdmp/kecamatan/kmp/{kmp}/verify-pelatihan', [\App\Http\Controllers\Kdmp\AdminKecamatanController::class, 'verifyPelatihan'])->name('admin.kdmp.kecamatan.verify_pelatihan');
    Route::post('/admin/kdmp/kecamatan/kmp-edit/{editRequest}/verify', [\App\Http\Controllers\Kdmp\AdminKecamatanController::class, 'verifyEditRequest'])->name('admin.kdmp.kecamatan.verify_edit');
    Route::get('/admin/kdmp/kecamatan/history', [\App\Http\Controllers\Kdmp\AdminKecamatanController::class, 'historyLog'])->name('admin.kdmp.kecamatan.history');

    // KDMP Admin KMP
    Route::get('/admin/kdmp/kmp', [\App\Http\Controllers\Kdmp\AdminKmpController::class, 'dashboard'])->name('admin.kdmp.kmp.dashboard');
    Route::post('/admin/kdmp/kmp/edit', [\App\Http\Controllers\Kdmp\AdminKmpController::class, 'requestEdit'])->name('admin.kdmp.kmp.request_edit');
    Route::post('/admin/kdmp/kmp/sertifikat', [\App\Http\Controllers\Kdmp\AdminKmpController::class, 'uploadSertifikat'])->name('admin.kdmp.kmp.upload_sertifikat');
    Route::post('/admin/kdmp/kmp/pelatihan', [\App\Http\Controllers\Kdmp\AdminKmpController::class, 'uploadPelatihan'])->name('admin.kdmp.kmp.upload_pelatihan');
    Route::post('/admin/kdmp/kmp/management', [\App\Http\Controllers\Kdmp\AdminKmpController::class, 'updateManagement'])->name('admin.kdmp.kmp.update_management');
    Route::get('/admin/kdmp/kmp/history', [\App\Http\Controllers\Kdmp\AdminKmpController::class, 'historyLog'])->name('admin.kdmp.kmp.history');

    Route::get(
    '/layanan/pengaduan-bedulang',
    [UserComplaintController::class,'createBedulang']
)
->name('complaints.bedulang');

    Route::post('/complaints/bedulang', [UserComplaintController::class, 'storeBedulang'])
        ->name('complaints.store.bedulang');

    Route::patch(
        '/complaints/{complaint}/mark-sent',
        [UserComplaintController::class,'markAsSent']
    )
    ->name('complaints.mark.sent');

    Route::post('/my-complaints/{complaint}/review', [\App\Http\Controllers\UserComplaintController::class, 'storeReview'])
        ->name('user.complaints.review');

    Route::get('/layanan/kdmp/koperasi/{id}', [InformationServiceController::class, 'showKdmpKoperasi'])
        ->name('layanan.kdmp.koperasi');

    Route::get('/layanan/kdmp/kecamatan/{nama}', [InformationServiceController::class, 'showKdmpKecamatan'])
        ->name('layanan.kdmp.kecamatan');

    Route::get('/layanan/data-sektoral', function () {
        return redirect()->route('data-sektoral.index');
    });


    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/pimpinan', [PimpinanDashboardController::class, 'index'])
        ->name('pimpinan.dashboard');

    Route::get('/pimpinan/koperasi', [KoperasiPimpinanController::class, 'detail'])
        ->name('pimpinan.koperasi.detail');

    // Admin Koperasi routes
    Route::get('/admin/koperasi', [KoperasiImportController::class, 'index'])
        ->name('admin.koperasi.index');
    Route::post('/admin/koperasi/import', [KoperasiImportController::class, 'import'])
        ->name('admin.koperasi.import');
    Route::get('/admin/koperasi/template', [KoperasiImportController::class, 'downloadTemplate'])
        ->name('admin.koperasi.template');
    Route::put('/admin/koperasi/{koperasi}', [KoperasiImportController::class, 'update'])
        ->name('admin.koperasi.update');
    Route::delete('/admin/koperasi/{koperasi}', [KoperasiImportController::class, 'destroy'])
        ->name('admin.koperasi.destroy');
    Route::delete('/admin/koperasi/log/{log}', [KoperasiImportController::class, 'destroyLog'])
        ->name('admin.koperasi.destroy_log');

    // Admin Kependudukan routes
    Route::get('/admin/kependudukan', [KependudukanImportController::class, 'index'])
        ->name('admin.kependudukan.index');
    Route::post('/admin/kependudukan/import', [KependudukanImportController::class, 'import'])
        ->name('admin.kependudukan.import');
    Route::get('/admin/kependudukan/template/{category}', [KependudukanImportController::class, 'downloadTemplate'])
        ->name('admin.kependudukan.template');
    Route::delete('/admin/kependudukan/log/{log}', [KependudukanImportController::class, 'destroyLog'])
        ->name('admin.kependudukan.destroy_log');

    Route::get('/dashboard/pad', [PimpinanDashboardController::class, 'pad'])
        ->name('pimpinan.pad');

    Route::get('/my-complaints', [\App\Http\Controllers\UserComplaintController::class, 'index'])
        ->name('user.complaints.index');

    Route::patch('/my-complaints/{complaint}/cancel', [\App\Http\Controllers\UserComplaintController::class, 'cancel'])
        ->name('user.complaints.cancel');

    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    // Data Sektoral
    Route::get('/data-sektoral', [DataSektoralController::class, 'index'])
        ->name('data-sektoral.index');
    Route::get('/api/data-sektoral/urusan', [DataSektoralController::class, 'getUrusan'])
        ->name('data-sektoral.urusan');
    Route::get('/api/data-sektoral/sub-urusan/{urusan}', [DataSektoralController::class, 'getSubUrusan'])
        ->name('data-sektoral.sub-urusan');
    Route::get('/api/data-sektoral/indikator/{urusan}/{subUrusan}', [DataSektoralController::class, 'getIndikator'])
        ->name('data-sektoral.indikator');
    Route::get('/api/data-sektoral/data/{urusan}/{subUrusan}/{indikator}/{tahunAwal}/{tahunAkhir}', [DataSektoralController::class, 'getData'])
        ->name('data-sektoral.data');
    Route::get('/api/data-sektoral/download-pdf/{urusan}/{subUrusan}/{indikator}/{tahunAwal}/{tahunAkhir}', [DataSektoralController::class, 'downloadPdf'])
        ->name('data-sektoral.download-pdf');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});

Route::get('/layanan/pariwisata/destinasi/{id}', [InformationServiceController::class, 'showPariwisataDestinasi'])
    ->name('layanan.pariwisata.destinasi');

Route::get('/layanan/{slug}', [InformationServiceController::class, 'show'])
    ->name('layanan.show');


require __DIR__.'/auth.php';

