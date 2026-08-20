<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BerandaController;

// Public Routes (Bisa diakses tanpa login)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/google', [AuthController::class, 'loginGoogle']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyForgotPasswordOtp']);
Route::post('/forgot-password/reset', [AuthController::class, 'resetForgotPassword']);
Route::get('/banners', [BerandaController::class, 'banners']);
Route::get('/services', [BerandaController::class, 'services']);
Route::get('/unit-pelayanan', [BerandaController::class, 'unitPelayanan']);
    Route::get('/announcements', [BerandaController::class, 'announcements']);
    
    // Berita & Pengumuman Lengkap
    Route::get('/news', [\App\Http\Controllers\Api\NewsApiController::class, 'index']);
    Route::get('/news/{id}', [\App\Http\Controllers\Api\NewsApiController::class, 'show']);

    // Pasar Daerah (Toko BUMDes)
    Route::get('/pasar-daerah/products', [\App\Http\Controllers\Api\PasarDaerahApiController::class, 'getProducts']);
    Route::get('/pasar-daerah/products/{id}', [\App\Http\Controllers\Api\PasarDaerahApiController::class, 'getProductDetail']);
    Route::get('/pasar-daerah/categories', [\App\Http\Controllers\Api\PasarDaerahApiController::class, 'getCategories']);

    // Wilayah (Regions) - Public for registration
    Route::get('/kemitraan/regions', [\App\Http\Controllers\Api\PartnerApplicationApiController::class, 'getRegions']);

// Protected Routes (Harus mengirimkan Bearer Token dari hasil Login)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/profile/password', [AuthController::class, 'updatePassword']);
    Route::post('/fcm-token', [AuthController::class, 'updateFcmToken']);
    // Kemitraan
    Route::post('/kemitraan/gabung', [\App\Http\Controllers\Api\PartnerApplicationApiController::class, 'store']);

    // KYC
    Route::post('/kyc/process', [\App\Http\Controllers\Api\KycController::class, 'process']);
    Route::post('/kyc/submit', [\App\Http\Controllers\Api\KycController::class, 'submit']);
    
    // Laporan
    Route::post('/laporan', [\App\Http\Controllers\Api\LaporanController::class, 'store']);
    Route::get('/my-reports', [\App\Http\Controllers\Api\LaporanController::class, 'getMyReports']);
    Route::get('/admin-reports', [\App\Http\Controllers\Api\LaporanController::class, 'getAdminReports']);
    Route::post('/admin-reports/{id}/forward', [\App\Http\Controllers\Api\LaporanController::class, 'forwardReport']);
    
    // Gas
    Route::get('/gas', [\App\Http\Controllers\Api\GasController::class, 'index']);
    Route::post('/gas/booking', [\App\Http\Controllers\Api\GasBookingController::class, 'store']);
    Route::post('/gas/payment/{id}', [\App\Http\Controllers\Api\GasBookingController::class, 'uploadPayment']);
    Route::get('/gas/receipt/{id}', [\App\Http\Controllers\Api\GasBookingController::class, 'getReceipt']);

    Route::get('/history', [\App\Http\Controllers\Api\HistoryController::class, 'index']);

    // Notifikasi
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/delete-all', [\App\Http\Controllers\Api\NotificationController::class, 'deleteAll']);
    
    // Chatbot (Gemini AI)
    Route::post('/chatbot', [\App\Http\Controllers\User\ChatbotController::class, 'ask']);

    // Pasar Daerah (Cart & Checkout)
    Route::get('/pasar-daerah/cart', [\App\Http\Controllers\Api\PasarDaerahApiController::class, 'getCart']);
    Route::post('/pasar-daerah/cart/add', [\App\Http\Controllers\Api\PasarDaerahApiController::class, 'addToCart']);
    Route::post('/pasar-daerah/cart/update', [\App\Http\Controllers\Api\PasarDaerahApiController::class, 'updateCart']);
    Route::post('/pasar-daerah/cart/remove', [\App\Http\Controllers\Api\PasarDaerahApiController::class, 'removeFromCart']);
    Route::post('/pasar-daerah/checkout', [\App\Http\Controllers\Api\PasarDaerahApiController::class, 'checkout']);
    Route::get('/pasar-daerah/orders/{id}/payment', [\App\Http\Controllers\Api\PasarDaerahApiController::class, 'getOrderPayment']);

    // Mutasi Domisili
    Route::get('/mutasi', [\App\Http\Controllers\Api\DomicileTransferApiController::class, 'index']);
    Route::post('/mutasi', [\App\Http\Controllers\Api\DomicileTransferApiController::class, 'store']);
    Route::delete('/mutasi/{id}', [\App\Http\Controllers\Api\DomicileTransferApiController::class, 'cancel']);

    // Event Gotong Royong
    Route::get('/events', [\App\Http\Controllers\Api\CommunityEventApiController::class, 'index']);
    Route::post('/events', [\App\Http\Controllers\Api\CommunityEventApiController::class, 'store']);
    Route::post('/events/{id}/join', [\App\Http\Controllers\Api\CommunityEventApiController::class, 'toggleJoin']);

    // Wilayah Admin Dashboard (RT/RW)
    Route::prefix('wilayah')->middleware('role:admin_rt,admin_rw,rt,rw')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\WilayahAdminApiController::class, 'getDashboardStats']);
        Route::get('/warga', [\App\Http\Controllers\Api\AdminWargaApiController::class, 'index']);
        Route::get('/warga/{id}', [\App\Http\Controllers\Api\AdminWargaApiController::class, 'show']);
        Route::post('/warga/{id}/approve-kyc', [\App\Http\Controllers\Api\AdminWargaApiController::class, 'approveKyc']);
        Route::post('/warga/{id}/reject-kyc', [\App\Http\Controllers\Api\AdminWargaApiController::class, 'rejectKyc']);
    });

    // Sewa Alat (Tenda, Kursi, Sound System, dll)
    Route::get('/rental/items', [\App\Http\Controllers\Api\RentalBookingApiController::class, 'index']);
    Route::get('/rental/items/{id}', [\App\Http\Controllers\Api\RentalBookingApiController::class, 'show']);
    Route::post('/rental/booking', [\App\Http\Controllers\Api\RentalBookingApiController::class, 'store']);
    Route::post('/rental/booking-package', [\App\Http\Controllers\Api\RentalBookingApiController::class, 'storePackage']);
    Route::get('/rental/my-bookings', [\App\Http\Controllers\Api\RentalBookingApiController::class, 'myBookings']);
    Route::delete('/rental/booking/{id}/cancel', [\App\Http\Controllers\Api\RentalBookingApiController::class, 'cancel']);

    // Sewa Kendaraan (Mobil, Pick-Up)
    Route::get('/mobil', [\App\Http\Controllers\Api\MobilBookingApiController::class, 'index']);
    Route::get('/mobil/{id}', [\App\Http\Controllers\Api\MobilBookingApiController::class, 'show']);
    Route::post('/mobil/booking', [\App\Http\Controllers\Api\MobilBookingApiController::class, 'store']);
    Route::get('/mobil/my-bookings', [\App\Http\Controllers\Api\MobilBookingApiController::class, 'myBookings']);
    Route::delete('/mobil/booking/{id}/cancel', [\App\Http\Controllers\Api\MobilBookingApiController::class, 'cancel']);

    // Sewa Fasilitas Umum (Gedung, Lapangan, Ambulans)
    Route::get('/fasilitas', [\App\Http\Controllers\Api\FasilitasBookingApiController::class, 'index']);
    Route::get('/fasilitas/{id}', [\App\Http\Controllers\Api\FasilitasBookingApiController::class, 'show']);
    Route::post('/fasilitas/booking', [\App\Http\Controllers\Api\FasilitasBookingApiController::class, 'store']);
    Route::get('/fasilitas/my-bookings', [\App\Http\Controllers\Api\FasilitasBookingApiController::class, 'myBookings']);
    Route::delete('/fasilitas/booking/{id}/cancel', [\App\Http\Controllers\Api\FasilitasBookingApiController::class, 'cancel']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
