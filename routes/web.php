<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\ClientNoteController;
use App\Http\Controllers\ServiceTemplateController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

// Auth routes (bez middleware)
Route::get('/auth/setup', [AuthController::class, 'showSetup'])->name('auth.setup');
Route::post('/auth/setup', [AuthController::class, 'storeSetup'])->name('auth.setup.store');
Route::get('/auth/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login.store');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

// Protected routes
Route::middleware(['check.app.password'])->group(function () {
    // Home page with statistics
    Route::get('/', [HomeController::class, 'index'])->name('home');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    
    // Calendar routes
    Route::get('/calendar', [AppointmentController::class, 'index'])->name('calendar.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::post('/appointments/check-availability', [AppointmentController::class, 'checkAvailability'])->name('appointments.check-availability');

Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
Route::post('/clients/{client}/notes', [ClientNoteController::class, 'store'])->name('clients.notes.store');
Route::put('/notes/{note}', [ClientNoteController::class, 'update'])->name('clients.notes.update');
Route::delete('/notes/{note}', [ClientNoteController::class, 'destroy'])->name('clients.notes.destroy');
Route::post('/product-groups', [ProductGroupController::class, 'store'])->name('product-groups.store');
Route::put('/product-groups/{productGroup}', [ProductGroupController::class, 'update'])->name('product-groups.update');
Route::delete('/product-groups/{productGroup}', [ProductGroupController::class, 'destroy'])->name('product-groups.destroy');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
Route::post('/products/{product}/adjust', [StockAdjustmentController::class, 'store'])->name('products.adjust');
Route::get('/products/bulk-receipt', [StockAdjustmentController::class, 'bulkReceiptForm'])->name('products.bulk-receipt');
Route::post('/products/adjust-batch', [StockAdjustmentController::class, 'storeBatch'])->name('products.adjust-batch');
Route::post('/service-templates', [ServiceTemplateController::class, 'store'])->name('service-templates.store');
Route::put('/service-templates/{serviceTemplate}', [ServiceTemplateController::class, 'update'])->name('service-templates.update');
Route::delete('/service-templates/{serviceTemplate}', [ServiceTemplateController::class, 'destroy'])->name('service-templates.destroy');
Route::get('/clients/{client}/visits/create', [VisitController::class, 'create'])->name('visits.create');
Route::get('/visits/{visit}', [VisitController::class, 'show'])->name('visits.show');
Route::post('/visits', [VisitController::class, 'store'])->name('visits.store');
    Route::post('/visits/{visit}/close', [VisitController::class, 'close'])->name('visits.close');
    Route::post('/visits/{visit}/duplicate', [VisitController::class, 'duplicate'])->name('visits.duplicate');
    
    // Settings routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/backup', [SettingsController::class, 'backup'])->name('settings.backup');
    Route::post('/settings/restore', [SettingsController::class, 'restore'])->name('settings.restore');
    Route::get('/settings/template', [SettingsController::class, 'downloadTemplate'])->name('settings.template');
    Route::post('/settings/import', [SettingsController::class, 'importProducts'])->name('settings.import');
});
