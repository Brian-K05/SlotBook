<?php

use App\Http\Controllers\Admin\BookingActionController;
use App\Http\Controllers\Admin\WeekController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PublicCalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicCalendarController::class)->name('home');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'create'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/admin/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/admin', WeekController::class)->name('admin.week');
    Route::post('/admin/bookings/{booking}/confirm', [BookingActionController::class, 'confirm'])->name('admin.bookings.confirm');
    Route::post('/admin/bookings/{booking}/cancel', [BookingActionController::class, 'cancel'])->name('admin.bookings.cancel');
    Route::post('/admin/bookings/{booking}/paid', [BookingActionController::class, 'togglePaid'])->name('admin.bookings.paid');
});
