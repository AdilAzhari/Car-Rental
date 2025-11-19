<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\CarController;
use App\Http\Controllers\Web\ReservationController;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Simple status route for testing
Route::get('/status', fn () => response()->json([
    'status' => 'OK',
    'timestamp' => now()->toISOString(),
    'database' => 'Connected',
    'app_name' => config('app.name'),
    'environment' => config('app.env'),
]));

// Homepage - shows featured cars
Route::get('/', [CarController::class, 'index']);

// Car routes
Route::get('/cars', [CarController::class, 'listing']);
Route::get('/cars/{id}', [CarController::class, 'show']);

// Reservation routes
Route::get('/reservations/create', [ReservationController::class, 'create']);
Route::get('/cars/{id}/reserve', [ReservationController::class, 'reserve'])->name('cars.reserve');

// Payment routes (payment checkout and return)
Route::get('/booking/{booking}/payment', [BookingController::class, 'paymentCheckout'])->name('booking.payment.checkout');
Route::get('/booking/payment/return/{booking}', [BookingController::class, 'paymentReturn'])->name('booking.payment.return');
Route::get('/booking/payment/success/{booking}', [BookingController::class, 'paymentSuccess'])->name('booking.payment.success');
Route::get('/booking/payment/cancel/{booking}', [BookingController::class, 'paymentCancel'])->name('booking.payment.cancel');

// Authenticated routes
Route::middleware('auth')->group(function (): void {
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('my-bookings.index');
    Route::get('/my-bookings/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/auth.php';
