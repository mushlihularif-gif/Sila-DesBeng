<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UnitPenyewaanController;
use App\Http\Controllers\Admin\GasController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SystemSettingController;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\Admin\ProfileController;

Route::get('/media/avatar/{filename}', [MediaController::class, 'adminAvatar'])->name('media.avatar');
Route::get('/media/profile/{filename}', [MediaController::class, 'userProfile'])->name('media.profile');

Route::get('/', function () {
    return redirect('beranda');
});

Route::get('/beranda', [App\Http\Controllers\User\BerandaController::class, 'index'])
    ->name('beranda')
    ->middleware('role:user,guest');

// Kabar Daerah / Pengumuman
Route::get('/kabar-daerah', [App\Http\Controllers\User\AnnouncementController::class, 'index'])
    ->name('announcements.index')
    ->middleware('role:user,guest');
Route::get('/kabar-daerah/{id}', [App\Http\Controllers\User\AnnouncementController::class, 'show'])
    ->name('announcements.show')
    ->middleware('role:user,guest');


Route::get('/pelayanan', function () {
    return view('users.pelayanan');
})->name('pelayanan')
  ->middleware('role:user,guest');


Route::get('/layanandaerah/profil-layanan', [App\Http\Controllers\User\RegionDirectoryController::class, 'index'])
    ->name('bumdes.profil')
    ->middleware('role:user,guest');

Route::get('/layanandaerah/profil-layanan/kecamatan/{id}', [App\Http\Controllers\User\RegionDirectoryController::class, 'showDesa'])
    ->name('bumdes.profil.desa')
    ->middleware('role:user,guest');

Route::get('/layanandaerah/laporan', [App\Http\Controllers\User\BumdesLaporanController::class, 'index'])
    ->name('bumdes.laporan')
    ->middleware('role:user,guest');

Route::get('/layanandaerah/{slug}', [App\Http\Controllers\User\BumdesUserController::class, 'show'])
    ->name('bumdes.detail')
    ->middleware('role:user,guest');


Route::get('/profil-siladesbeng', [App\Http\Controllers\User\IsewaProfileController::class, 'index'])
    ->name('siladesbeng.profile')
    ->middleware('role:user,guest');

Route::get('/kemitraan/gabung', [App\Http\Controllers\PartnerApplicationController::class, 'create'])
    ->name('kemitraan.create')
    ->middleware('role:user,guest');
Route::post('/kemitraan/gabung', [App\Http\Controllers\PartnerApplicationController::class, 'store'])
    ->name('kemitraan.store')
    ->middleware('role:user,guest');



Route::get('/unit-penyewaan-alat', [App\Http\Controllers\User\RentalUserController::class, 'index'])
    ->name('rental.equipment')
    ->middleware(['role:user,guest', 'region.service:penyewaan-alat']);
Route::get('/unit-penyewaan-alat/{id}', [App\Http\Controllers\User\RentalUserController::class, 'show'])
    ->name('rental.equipment.show')
    ->middleware('role:user,guest');


Route::get('/unit-penyewaan-alat/{id}/booking', [App\Http\Controllers\User\RentalBookingController::class, 'create'])
    ->name('rental.booking')
    ->middleware('auth');
Route::post('/rental/booking', [App\Http\Controllers\User\RentalBookingController::class, 'store'])
    ->name('rental.booking.store')
    ->middleware('auth');

Route::get('/unit-penyewaan-mobil', [App\Http\Controllers\User\MobilRentalUserController::class, 'index'])
    ->name('mobil.rental.equipment')
    ->middleware(['role:user,guest', 'region.service:penyewaan-mobil']);
Route::get('/unit-penyewaan-mobil/{id}', [App\Http\Controllers\User\MobilRentalUserController::class, 'show'])
    ->name('mobil.rental.show')
    ->middleware('role:user,guest');
Route::get('/unit-penyewaan-mobil/{id}/booking', [App\Http\Controllers\User\MobilBookingController::class, 'create'])
    ->name('mobil.rental.booking')
    ->middleware('auth');
Route::post('/mobil-rental/booking', [App\Http\Controllers\User\MobilBookingController::class, 'store'])
    ->name('mobil.rental.booking.store')
    ->middleware('auth');

Route::get('/unit-peminjaman-fasilitas-umum', [App\Http\Controllers\User\FasilitasUmumUserController::class, 'index'])
    ->name('user.fasilitas-umum.equipment')
    ->middleware(['role:user,guest', 'region.service:peminjaman-fasilitas-umum']);
Route::get('/unit-peminjaman-fasilitas-umum/{id}', [App\Http\Controllers\User\FasilitasUmumUserController::class, 'show'])
    ->name('user.fasilitas-umum.show')
    ->middleware('role:user,guest');
Route::get('/unit-peminjaman-fasilitas-umum/{id}/booking', [App\Http\Controllers\User\FasilitasUmumBookingController::class, 'create'])
    ->name('user.fasilitas-umum.book')
    ->middleware('auth');
Route::post('/fasilitas-umum/booking', [App\Http\Controllers\User\FasilitasUmumBookingController::class, 'store'])
    ->name('user.fasilitas-umum.book.store')
    ->middleware('auth');


Route::get('/unit-penjualan-gas', [App\Http\Controllers\User\GasSalesUserController::class, 'index'])
    ->name('gas.sales')
    ->middleware(['role:user,guest', 'region.service:penjualan-gas']);
Route::get('/unit-penjualan-gas/{id}', [App\Http\Controllers\User\GasSalesUserController::class, 'show'])
    ->name('gas.sales.show')
    ->middleware('role:user,guest');
Route::get('/unit-penjualan-gas/{id}/booking', [App\Http\Controllers\User\GasSalesUserController::class, 'booking'])
    ->name('gas.booking')
    ->middleware('auth');
Route::post('/gas/booking', [App\Http\Controllers\User\GasBookingController::class, 'store'])
    ->name('gas.booking.store')
    ->middleware('auth');

Route::get('/gas/payment/{id}', [App\Http\Controllers\User\GasBookingController::class, 'payment'])
    ->name('user.gas.payment')
    ->middleware('auth');

Route::get('/gas/booking/{id}/pending', [App\Http\Controllers\User\GasBookingController::class, 'paymentPending'])
    ->name('user.gas.payment.pending')
    ->middleware('auth');

Route::post('/gas/payment/{id}/simulate', [App\Http\Controllers\User\GasBookingController::class, 'simulatePayment'])
    ->name('user.gas.payment.simulate')
    ->middleware('auth');

Route::post('/gas/payment/{id}/cancel', [App\Http\Controllers\User\GasBookingController::class, 'cancelPayment'])
    ->name('user.gas.payment.cancel')
    ->middleware('auth');

Route::post('/gas/payment/{id}/change-method', [App\Http\Controllers\User\GasBookingController::class, 'changePaymentMethod'])
    ->name('user.gas.payment.change_method')
    ->middleware('auth');

Route::get('/aktivitas', [App\Http\Controllers\User\ActivityController::class, 'index'])
    ->name('user.activity')
    ->middleware('role:user');
Route::post('/aktivitas/{type}/{id}/cancel', [App\Http\Controllers\User\ActivityController::class, 'requestCancellation'])
    ->name('user.activity.cancel')
    ->middleware('role:user');
Route::delete('/aktivitas/clear-all/{type}', [App\Http\Controllers\User\ActivityController::class, 'clearAll'])
    ->name('user.activity.clearAll')
    ->middleware('role:user');

Route::delete('/aktivitas/{type}/{id}', [App\Http\Controllers\User\ActivityController::class, 'destroy'])
    ->name('user.activity.destroy')
    ->middleware('role:user');


Route::get('/notifikasi', [App\Http\Controllers\User\NotificationController::class, 'index'])
    ->name('user.notifications')
    ->middleware('role:user');
Route::post('/notifikasi/{id}/read', [App\Http\Controllers\User\NotificationController::class, 'markAsRead'])
    ->name('user.notifications.read')
    ->middleware('role:user');
Route::post('/notifikasi/read-all', [App\Http\Controllers\User\NotificationController::class, 'markAllAsRead'])
    ->name('user.notifications.readAll')
    ->middleware('role:user');
Route::delete('/notifikasi/hapus-semua', [App\Http\Controllers\User\NotificationController::class, 'deleteAll'])
    ->name('user.notifications.deleteAll')
    ->middleware('role:user');

// Route Bukti Transaksi
Route::get('/receipt/rental/{id}/view', [App\Http\Controllers\User\ReceiptController::class, 'viewRentalReceipt'])
    ->name('receipt.rental.view')
    ->middleware('role:user,admin');
Route::get('/receipt/rental/{id}/download', [App\Http\Controllers\User\ReceiptController::class, 'downloadRentalReceipt'])
    ->name('receipt.rental.download')
    ->middleware('role:user,admin');
Route::get('/receipt/gas/{id}/view', [App\Http\Controllers\User\ReceiptController::class, 'viewGasReceipt'])
    ->name('receipt.gas.view')
    ->middleware('role:user,admin');
Route::get('/receipt/gas/{id}/download', [App\Http\Controllers\User\ReceiptController::class, 'downloadGasReceipt'])
    ->name('receipt.gas.download')
    ->middleware('role:user,admin');



Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register')->middleware('throttle:100,1');
Route::get('/auth/otp', [AuthController::class, 'showOtpForm'])->name('auth.otp.view');
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify-otp')->middleware('throttle:100,10');
Route::get('/auth/sandbox-otp-display', [AuthController::class, 'showSandboxOtp'])->name('auth.sandbox.otp');
Route::post('/auth/resend-otp', [AuthController::class, 'resendOtp'])->name('auth.resend-otp')->middleware('throttle:3,5');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth');

Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password')->middleware('throttle:3,5');
Route::get('/auth/forgot-password/otp', [AuthController::class, 'showForgotOtpForm'])->name('auth.forgot-password.otp.view');
Route::post('/auth/forgot-password/verify-otp', [AuthController::class, 'verifyForgotPasswordOtp'])->name('auth.forgot-password.verify-otp')->middleware('throttle:100,5');
Route::get('/auth/forgot-password/reset', [AuthController::class, 'showResetPasswordForm'])->name('auth.forgot-password.reset.view');
Route::post('/auth/forgot-password/reset', [AuthController::class, 'resetForgotPassword'])->name('auth.forgot-password.reset')->middleware('throttle:100,5');
Route::post('/auth/forgot-password/resend-otp', [AuthController::class, 'resendForgotPasswordOtp'])->name('auth.forgot-password.resend-otp')->middleware('throttle:3,5');

// Google Auth Routes
Route::get('/auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/register-google', [App\Http\Controllers\Auth\GoogleController::class, 'showRegistrationForm'])->name('register.google');
Route::post('/auth/register-google', [App\Http\Controllers\Auth\GoogleController::class, 'completeRegistration'])->name('register.google.complete');


Route::get('/profile', [App\Http\Controllers\User\ProfileController::class, 'index'])
    ->name('profile')
    ->middleware('role:user');
Route::put('/profile', [App\Http\Controllers\User\ProfileController::class, 'update'])
    ->name('profile.update')
    ->middleware('role:user');


Route::post('/profile/change-password', [App\Http\Controllers\User\ProfileController::class, 'changePassword'])
    ->name('profile.change-password')
    ->middleware('role:user');
Route::post('/profile/verify-otp', [App\Http\Controllers\User\ProfileController::class, 'verifyOtp'])
    ->name('profile.verify-otp')
    ->middleware(['role:user', 'throttle:5,10']);
Route::post('/profile/resend-otp', [App\Http\Controllers\User\ProfileController::class, 'resendOtp'])
    ->name('profile.resend-otp')
    ->middleware(['role:user', 'throttle:3,5']);


// Autentikasi Admin menggunakan route API yang sama dengan autentikasi pengguna
// Pengguna Admin login melalui /auth/login dan dialihkan berdasarkan peran (role)


Route::get('/files/{id}/{action}', function ($id, $action) {
    $file = \App\Models\File::findOrFail($id);
    return $file->handleAction($action);
})->name('files.action')->where('action', 'stream|download')->middleware('auth');

Route::get('/maintenance', [DashboardController::class, 'maintenance'])->name('maintenance');




// Route Admin
Route::prefix('admin')->middleware('role:admin')->group(function () {
    // Dashboard (Papan Kontrol)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/search', [DashboardController::class, 'globalSearch'])->name('admin.search');
    
    // Manajemen Kemitraan Daerah
    Route::get('/kemitraan', [\App\Http\Controllers\Admin\PartnerApplicationController::class, 'index'])->name('admin.kemitraan.index');
    Route::post('/kemitraan/{id}/approve', [\App\Http\Controllers\Admin\PartnerApplicationController::class, 'approve'])->name('admin.kemitraan.approve');
    Route::post('/kemitraan/{id}/reject', [\App\Http\Controllers\Admin\PartnerApplicationController::class, 'reject'])->name('admin.kemitraan.reject');
    
    // Pengaturan
    Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings');
    Route::post('/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::get('/settings/connections', [DashboardController::class, 'connections'])->name('admin.settings.connections');
    Route::get('/settings/notifications', [DashboardController::class, 'notifications'])->name('admin.settings.notifications');
    Route::post('/settings/notifications', [DashboardController::class, 'notificationsUpdate'])->name('admin.settings.notifications.update');
    
    // Pengaturan Wilayah & Layanan (Kas Independen)
    Route::get('/region-settings', [\App\Http\Controllers\Admin\RegionSettingController::class, 'index'])->name('admin.region-settings.index');
    Route::post('/region-settings', [\App\Http\Controllers\Admin\RegionSettingController::class, 'update'])->name('admin.region-settings.update');
    
    // Manajemen Banner / Iklan
    Route::get('/banners', [\App\Http\Controllers\Admin\BannerController::class, 'index'])->name('admin.banners.index');
    Route::post('/banners', [\App\Http\Controllers\Admin\BannerController::class, 'store'])->name('admin.banners.store');
    Route::put('/banners/{id}', [\App\Http\Controllers\Admin\BannerController::class, 'update'])->name('admin.banners.update');
    Route::delete('/banners/{id}', [\App\Http\Controllers\Admin\BannerController::class, 'destroy'])->name('admin.banners.destroy');
    
    // Manajemen Pengumuman & Event
    Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class)->names([
        'index' => 'admin.announcements.index',
        'create' => 'admin.announcements.create',
        'store' => 'admin.announcements.store',
        'show' => 'admin.announcements.show',
        'edit' => 'admin.announcements.edit',
        'update' => 'admin.announcements.update',
        'destroy' => 'admin.announcements.destroy',
    ]);
    
    // Route untuk Profil Admin (menggunakan ProfileController)
    Route::get('/profile', [ProfileController::class, 'index'])->name('admin.profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('admin.profile.delete-avatar');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('admin.profile.change-password');
    Route::post('/profile/verify-otp', [ProfileController::class, 'verifyOtp'])->name('admin.profile.verify-otp');
    Route::post('/admin/profile/resend-otp', [App\Http\Controllers\Admin\ProfileController::class, 'resendOtp'])->name('admin.profile.resend-otp');

    // Route untuk Manajemen Pengguna
    Route::get('/manajemen-pengguna', [UserManagementController::class, 'index'])->name('admin.manajemen-pengguna.index');
    Route::get('/manajemen-pengguna/{user}', [UserManagementController::class, 'show'])->name('admin.manajemen-pengguna.show');
    Route::put('/manajemen-pengguna/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('admin.manajemen-pengguna.toggle-status');

    // Route untuk Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::get('/notifications/create', [NotificationController::class, 'create'])->name('admin.notifications.create');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('admin.notifications.store');
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('admin.notifications.mark-as-read');
    Route::put('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-read');
    Route::delete('/notifications/hapus-semua', [NotificationController::class, 'deleteAll'])->name('admin.notifications.deleteAll');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('admin.notifications.destroy');

    // Route untuk Pengaturan Sistem
    Route::get('/pengaturan-sistem', [SystemSettingController::class, 'index'])->name('admin.system-settings.index');
    Route::put('/pengaturan-sistem', [SystemSettingController::class, 'update'])->name('admin.system-settings.update');
    Route::delete('/pengaturan-sistem/reset', [SystemSettingController::class, 'reset'])->name('admin.system-settings.reset');
    
    // Route Unit
    Route::prefix('unit')->group(function () {
        // Penyewaan Alat
        Route::resource('penyewaan', UnitPenyewaanController::class)->names([
            'index' => 'admin.unit.penyewaan.index',
            'create' => 'admin.unit.penyewaan.create',
            'store' => 'admin.unit.penyewaan.store',
            'show' => 'admin.unit.penyewaan.show',
            'edit' => 'admin.unit.penyewaan.edit',
            'update' => 'admin.unit.penyewaan.update',
            'destroy' => 'admin.unit.penyewaan.destroy',
        ]);

        // Penyewaan Mobil
        Route::resource('mobil', \App\Http\Controllers\Admin\UnitPenyewaanMobilController::class)->names([
            'index' => 'admin.unit.mobil.index',
            'create' => 'admin.unit.mobil.create',
            'store' => 'admin.unit.mobil.store',
            'show' => 'admin.unit.mobil.show',
            'edit' => 'admin.unit.mobil.edit',
            'update' => 'admin.unit.mobil.update',
            'destroy' => 'admin.unit.mobil.destroy',
        ]);

        // Penjualan Gas
        Route::resource('gas', GasController::class)->names([
            'index' => 'admin.unit.penjualan_gas.index',
            'create' => 'admin.unit.penjualan_gas.create',
            'store' => 'admin.unit.penjualan_gas.store',
            'show' => 'admin.unit.penjualan_gas.show',
            'edit' => 'admin.unit.penjualan_gas.edit',
            'update' => 'admin.unit.penjualan_gas.update',
            'destroy' => 'admin.unit.penjualan_gas.destroy',
        ]);

        // Fasilitas Umum
        Route::resource('fasilitas_umum', \App\Http\Controllers\Admin\UnitFasilitasUmumController::class)->names([
            'index' => 'admin.unit.fasilitas_umum.index',
            'create' => 'admin.unit.fasilitas_umum.create',
            'store' => 'admin.unit.fasilitas_umum.store',
            'show' => 'admin.unit.fasilitas_umum.show',
            'edit' => 'admin.unit.fasilitas_umum.edit',
            'update' => 'admin.unit.fasilitas_umum.update',
            'destroy' => 'admin.unit.fasilitas_umum.destroy',
        ]);
    });
    
    // Route Aktivitas
    Route::prefix('aktivitas')->group(function () {
        Route::get('/kajian', [DashboardController::class, 'index'])->name('admin.aktivitas.kajian.index');
        Route::get('/transaksi', [DashboardController::class, 'index'])->name('admin.aktivitas.transaksi.index');
        Route::get('/kemitraan', [DashboardController::class, 'index'])->name('admin.aktivitas.kemitraan.index');

        Route::get('/permintaan-pengajuan/notification-counts', [\App\Http\Controllers\Admin\RequestController::class, 'getCounts'])->name('admin.aktivitas.permintaan-pengajuan.counts');
        Route::get('/permintaan-pengajuan', [\App\Http\Controllers\Admin\RequestController::class, 'index'])->name('admin.aktivitas.permintaan-pengajuan.index');
        Route::get('/permintaan-pengajuan/{id}/{type}', [\App\Http\Controllers\Admin\RequestController::class, 'show'])->name('admin.aktivitas.permintaan-pengajuan.show');
        Route::post('/permintaan-pengajuan/{id}/{type}/approve', [\App\Http\Controllers\Admin\RequestController::class, 'approve'])->name('admin.aktivitas.permintaan-pengajuan.approve');
        Route::post('/permintaan-pengajuan/{id}/{type}/reject', [\App\Http\Controllers\Admin\RequestController::class, 'reject'])->name('admin.aktivitas.permintaan-pengajuan.reject');
        
        // Route Manajemen Status Pesanan
        Route::post('/permintaan-pengajuan/{type}/{id}/update-status', [\App\Http\Controllers\Admin\RequestController::class, 'updateStatus'])->name('admin.aktivitas.update-status');
        Route::post('/permintaan-pengajuan/rental/{id}/return', [\App\Http\Controllers\Admin\RequestController::class, 'returnRental'])->name('admin.aktivitas.permintaan-pengajuan.return');
        
        Route::post('/permintaan-pengajuan/{type}/{id}/delivery-proof', [\App\Http\Controllers\Admin\RequestController::class, 'uploadDeliveryProof'])->name('admin.aktivitas.delivery-proof');
        Route::post('/permintaan-pengajuan/{type}/{id}/cancellation/{action}', [\App\Http\Controllers\Admin\RequestController::class, 'handleCancellation'])->name('admin.aktivitas.cancellation');

        Route::get('/bukti-transaksi', [\App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('admin.aktivitas.bukti-transaksi.index');
        Route::get('/bukti-transaksi/{id}/{type}', [\App\Http\Controllers\Admin\TransactionController::class, 'show'])->name('admin.aktivitas.bukti-transaksi.show');
        Route::post('/bukti-transaksi/{id}/{type}/verify', [\App\Http\Controllers\Admin\TransactionController::class, 'verify'])->name('admin.aktivitas.bukti-transaksi.verify');
        Route::post('/bukti-transaksi/{id}/{type}/reject', [\App\Http\Controllers\Admin\TransactionController::class, 'reject'])->name('admin.aktivitas.bukti-transaksi.reject');
        Route::post('/bukti-transaksi/{id}/{type}/update-status/{status}', [\App\Http\Controllers\Admin\TransactionController::class, 'updateStatus'])->name('admin.aktivitas.bukti-transaksi.update-status');
        Route::get('/bukti-transaksi/{id}/{type}/download', [\App\Http\Controllers\Admin\TransactionController::class, 'downloadProof'])->name('admin.aktivitas.bukti-transaksi.download');
    });
    
    // Route Laporan
    Route::prefix('laporan')->group(function () {
        Route::get('/transaksi', [\App\Http\Controllers\Admin\ReportController::class, 'transactions'])->name('admin.laporan.transaksi');
        Route::get('/pendapatan', [\App\Http\Controllers\Admin\ReportController::class, 'income'])->name('admin.laporan.pendapatan');
        Route::post('/log/clear', [\App\Http\Controllers\Admin\ReportController::class, 'clearLogs'])->name('admin.laporan.log.clear');
        Route::get('/log', [\App\Http\Controllers\Admin\ReportController::class, 'logs'])->name('admin.laporan.log');
        
        // Route Transaksi Manual
        Route::post('/manual-transaction', [\App\Http\Controllers\Admin\ReportController::class, 'storeManualTransaction'])->name('admin.laporan.manual.store');
        Route::put('/manual-transaction/{id}', [\App\Http\Controllers\Admin\ReportController::class, 'updateManualTransaction'])->name('admin.laporan.manual.update');
        Route::delete('/manual-transaction/{id}', [\App\Http\Controllers\Admin\ReportController::class, 'destroyManualTransaction'])->name('admin.laporan.manual.destroy');
    });
    
    // Route iSewa
    Route::prefix('isewa')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\Admin\SettingController::class, 'showIsewaProfile'])->name('admin.isewa.profile');
        Route::get('/developer/{name}', [\App\Http\Controllers\Admin\SettingController::class, 'showDeveloperProfile'])->name('admin.isewa.developer.profile');
        
        Route::get('/profil-pemerintah-desa', [\App\Http\Controllers\Admin\BumdesController::class, 'index'])->name('admin.isewa.profile-bumdes');
        Route::prefix('pemerintah-desa')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\BumdesController::class, 'index'])->name('admin.isewa.bumdes.index');
            Route::get('/create', [\App\Http\Controllers\Admin\BumdesController::class, 'create'])->name('admin.isewa.bumdes.create');
            Route::post('/', [\App\Http\Controllers\Admin\BumdesController::class, 'store'])->name('admin.isewa.bumdes.store');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\BumdesController::class, 'edit'])->name('admin.isewa.bumdes.edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\BumdesController::class, 'update'])->name('admin.isewa.bumdes.update');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\BumdesController::class, 'destroy'])->name('admin.isewa.bumdes.destroy');
        });
        Route::post('/bumdes/update-whatsapp', [\App\Http\Controllers\Admin\BumdesController::class, 'updateWhatsapp'])->name('admin.isewa.bumdes.update.whatsapp');
    });
});

// ==========================================
// ROUTES PELAPORAN WARGA (I_VILAGGE MERGE)
// ==========================================

Route::post('/kritik-saran', [\App\Http\Controllers\KritikSaranController::class, 'store'])->name('kritik-saran.store');

// Landing Page Pelaporan Warga (Tidak perlu login)
Route::get('/pelaporan-warga', function () {
    return view('user.laporan.landing');
})->name('pelaporan.landing')->middleware('region.service:pelaporan-warga');

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::prefix('user/laporan')->name('user.laporan.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LaporanController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\LaporanController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\LaporanController::class, 'store'])->name('store');
        Route::get('/export/{id}', [\App\Http\Controllers\LaporanController::class, 'exportPdf'])->name('export');
        Route::get('/{laporan}', [\App\Http\Controllers\LaporanController::class, 'show'])->name('show');
        Route::get('/{laporan}/edit', [\App\Http\Controllers\LaporanController::class, 'edit'])->name('edit');
        Route::put('/{laporan}', [\App\Http\Controllers\LaporanController::class, 'update'])->name('update');
        Route::delete('/{laporan}', [\App\Http\Controllers\LaporanController::class, 'destroy'])->name('destroy');
    });

    Route::post('/laporan/{laporan}/rating', [\App\Http\Controllers\RatingController::class, 'store'])->name('laporan.rating.store');
    Route::put('/laporan/{laporan}/rating', [\App\Http\Controllers\RatingController::class, 'update'])->name('laporan.rating.update');

    Route::prefix('help-center')->name('help-center.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HelpCenterController::class, 'index'])->name('index');
        Route::get('/tickets', [\App\Http\Controllers\HelpCenterController::class, 'myTickets'])->name('my-tickets');
        Route::get('/create', [\App\Http\Controllers\HelpCenterController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\HelpCenterController::class, 'store'])->name('store');
        Route::get('/tickets/{id}', [\App\Http\Controllers\HelpCenterController::class, 'show'])->name('show');
        Route::post('/tickets/{id}/close', [\App\Http\Controllers\HelpCenterController::class, 'close'])->name('close');
    });
    
    // Notifikasi pelaporan dipisah pathnya
    Route::prefix('laporan-notifications')->name('laporan-notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::match(['get', 'post', 'patch'], '/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear/read', [\App\Http\Controllers\NotificationController::class, 'clearRead'])->name('clear-read');
    });
});

Route::middleware(['auth', 'role:super_admin,admin_kecamatan,admin_desa,lurah'])->prefix('lurah')->name('lurah.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\LurahController::class, 'dashboard'])->name('dashboard');
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LurahController::class, 'indexLaporan'])->name('index');
        Route::get('/export-pdf', [\App\Http\Controllers\LurahController::class, 'exportPdf'])->name('export.dashboard');
        Route::get('/export/{id}', [\App\Http\Controllers\LurahController::class, 'exportDetailPdf'])->name('export.detail');
        Route::post('/{id}/status', [\App\Http\Controllers\LurahController::class, 'updateStatus'])->name('updateStatus');
        Route::get('/{id}', [\App\Http\Controllers\LurahController::class, 'showLaporan'])->name('show');
    });
    Route::get('/statistik', [\App\Http\Controllers\LurahController::class, 'statistik'])->name('statistik');
    Route::get('/settings', [\App\Http\Controllers\LurahController::class, 'settings'])->name('settings');
    Route::put('/profile/update', [\App\Http\Controllers\LurahController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\LurahController::class, 'updatePassword'])->name('profile.password');
});

// API Routes for Regions
Route::get('/api/regions', function () {
    return response()->json(\App\Models\Region::all());
});

Route::get('/dev/setup-region', function () {
    $user = \App\Models\User::first();
    if (!$user) return 'No user found';

    // Check if region exists
    $region = \App\Models\Region::where('name', 'Pematang Duku Timur')->first();
    if (!$region) {
        $region = \App\Models\Region::create([
            'name' => 'Pematang Duku Timur',
            'type' => 'desa',
            'profile_text' => 'Pemerintahan Desa Pematang Duku Timur, Kecamatan Bengkalis, Kabupaten Bengkalis.',
        ]);
    }
    
    // Assign to user
    $user->region_id = $region->id;
    $user->save();

    return 'Assigned Region ID: ' . $region->id . ' to User: ' . $user->email;
});

Route::get('/dev/fix-duplicates', function () {
    try {
        $correctKecamatan = \App\Models\Region::where('name', 'Kecamatan Bengkalis')->first();
        $correctDesa = \App\Models\Region::where('name', 'Desa Pematang Duku Timur')->first();
        
        $messages = [];

        if ($correctKecamatan && $correctDesa) {
            // Fix ALL Duplicate Kecamatan Bengkalis
            $duplicateKecamatans = \App\Models\Region::whereIn('name', ['Bengkalis', 'Kecamatan Bengkalis'])
                ->where('type', 'kecamatan')
                ->where('id', '!=', $correctKecamatan->id)
                ->get();
                
            foreach ($duplicateKecamatans as $dupKec) {
                // Move children
                \App\Models\Region::where('parent_id', $dupKec->id)->update(['parent_id' => $correctKecamatan->id]);
                // Move users
                \App\Models\User::where('region_id', $dupKec->id)->update(['region_id' => $correctKecamatan->id]);
                $dupKec->delete();
                $messages[] = 'Duplicate Kecamatan Bengkalis fixed (ID: ' . $dupKec->id . ').';
            }

            // Fix ALL Duplicate Desa Pematang Duku Timur
            $duplicates = \App\Models\Region::whereIn('name', ['Pematang Duku Timur', 'Desa Pematang Duku Timur'])
                ->where('type', 'desa')
                ->where('id', '!=', $correctDesa->id)
                ->get();
            
            foreach ($duplicates as $dup) {
                \App\Models\User::where('region_id', $dup->id)->update(['region_id' => $correctDesa->id]);
                // Move any child data (like services)
                foreach($dup->services as $service) {
                    $correctDesa->services()->syncWithoutDetaching([$service->id => ['is_active' => true]]);
                }
                $dup->delete();
                $messages[] = 'Duplicate Desa Pematang Duku Timur fixed (ID: ' . $dup->id . ').';
            }
            
            // Re-check how many desas are actually under correct kecamatan
            $totalDesa = \App\Models\Region::where('parent_id', $correctKecamatan->id)->where('type', 'desa')->count();
            $messages[] = "Total Desa under Kecamatan Bengkalis is now: " . $totalDesa;
        }
        
        return count($messages) > 0 ? implode('<br>', $messages) : 'No duplicates found or missing correct regions.';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/dev/check-regions', function () {
    $regions = \App\Models\Region::orderBy('type')->orderBy('name')->get();
    $html = '<table border="1" cellpadding="5"><tr><th>ID</th><th>Name</th><th>Type</th><th>Parent ID</th></tr>';
    foreach ($regions as $r) {
        $html .= "<tr><td>{$r->id}</td><td>{$r->name}</td><td>{$r->type}</td><td>{$r->parent_id}</td></tr>";
    }
    $html .= '</table>';
    return $html;
});
