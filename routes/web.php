<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\BillingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    
    // Customer routes
    Route::resource('customers', CustomerController::class);
    
    // Package routes
    Route::resource('packages', PackageController::class);
    
    // Billing routes
    Route::get('/billing/pending', [BillingController::class, 'pendingBills'])->name('billing.pending-bills');
    Route::get('/billing/all', [BillingController::class, 'allBills'])->name('billing.all');
    Route::get('/billing/customer/{customerId}', [BillingController::class, 'customerBillingHistory'])->name('billing.customer-history');
    Route::get('/billing/payment/{customerId}', [BillingController::class, 'showPaymentForm'])->name('billing.record-payment.show');
    Route::post('/billing/payment/{customerId}', [BillingController::class, 'recordPayment'])->name('billing.record-payment.store');
    Route::get('/billing/report', [BillingController::class, 'monthlyReport'])->name('billing.monthly-report');
    Route::post('/billing/generate', [BillingController::class, 'generateBills'])->name('billing.generate');
});
