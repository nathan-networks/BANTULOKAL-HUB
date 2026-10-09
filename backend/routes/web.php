<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceRequestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\AdminManagementController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('guest')
        ->name('login.submit');

    Route::middleware('auth', 'admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');

        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Service request management
        Route::get('/service-requests', [ServiceRequestController::class, 'index'])
            ->name('service-requests.index');

        Route::get('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'show'])
            ->name('service-requests.show');

        Route::patch('/service-requests/{serviceRequest}/review', [ServiceRequestController::class, 'review'])
            ->name('service-requests.review');

        // Manual payment verification
        Route::patch(
            '/service-requests/{serviceRequest}/verify-payment',
            [ServiceRequestController::class, 'verifyPayment']
        )->name('service-requests.verify-payment');

        Route::post('/service-requests/{serviceRequest}/resend-payment-confirmation', [ServiceRequestController::class, 'resendPaymentConfirmation'])->name('service-requests.resend-payment-confirmation');

        // Rental booking management
        Route::get('/bookings', [BookingController::class, 'index'])
            ->name('bookings.index');

        Route::get('/bookings/{booking}', [BookingController::class, 'show'])
            ->name('bookings.show');

        Route::patch('/bookings/{booking}/review', [BookingController::class, 'review'])
            ->name('bookings.review');

        Route::patch(
            '/bookings/{booking}/verify-payment',
            [BookingController::class, 'verifyPayment']
        )->name('bookings.verify-payment');

        Route::post(
            '/bookings/{booking}/resend-payment-confirmation',
            [BookingController::class, 'resendPaymentConfirmation']

            )->name('bookings.resend-payment-confirmation');

        // Admin account management
        Route::get('/admins', [AdminManagementController::class, 'index'])
            ->name('admins.index');

        Route::post('/admins', [AdminManagementController::class, 'store'])
            ->name('admins.store');

        Route::patch('/admins/{user}/status', [AdminManagementController::class, 'updateStatus'])
            ->name('admins.update-status');
    });

});
