<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\EquipmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\FreelancerApplicationController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\ServicePricingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/equipment', [EquipmentController::class, 'index']);
Route::get('/equipment/{id}', [EquipmentController::class, 'show'])
    ->whereNumber('id');

Route::post('/bookings', [BookingController::class, 'store']);

Route::post('/freelancer-applications', [FreelancerApplicationController::class, 'store']);

Route::post('/service-requests', [ServiceRequestController::class, 'store']);

Route::get('/service-pricing', [ServicePricingController::class, 'index']);