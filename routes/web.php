<?php

use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Officer\LoanReviewController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : view('welcome'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1');
    Route::get('/otp', [AuthController::class, 'showOtp'])->name('otp');
    Route::post('/otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
});

Route::middleware(['auth', 'notblocked'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:customer')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('/notifications/read', [NotificationController::class, 'readAll'])->name('notifications.read');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/accounts', [DashboardController::class, 'openAccount'])->name('accounts.open');
        Route::get('/transfer', [TransferController::class, 'create'])->name('transfer');
        Route::post('/transfer', [TransferController::class, 'store'])->middleware('throttle:30,1')->name('transfer.store');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
        Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
        Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
        Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    });

    Route::middleware('role:officer,admin')->prefix('officer')->group(function () {
        Route::get('/loans', [LoanReviewController::class, 'index'])->name('officer.loans');
        Route::post('/loans/{loan}/review', [LoanReviewController::class, 'review'])->name('officer.loans.review');
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/', [DashboardAdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/transactions', [DashboardAdminController::class, 'transactions'])->name('admin.transactions');
        Route::get('/users', [UserAdminController::class, 'index'])->name('admin.users');
        Route::post('/users/{user}/block', [UserAdminController::class, 'toggleBlock'])->name('admin.users.block');
        Route::post('/users/{user}/role', [UserAdminController::class, 'setRole'])->name('admin.users.role');
        Route::get('/audit', [UserAdminController::class, 'audit'])->name('admin.audit');
    });
});
