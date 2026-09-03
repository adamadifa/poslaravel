<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Protected Routes (Wajib Login)
Route::middleware(['auth'])->group(function () {
    
    // 1. Dashboard Utama
    Route::get('/dashboard', function () {
        return view('dashboard.index', [
            'headerTitle' => 'Dashboard'
        ]);
    })->name('dashboard');

    // 2. POS Routes
    Route::get('pos', [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
    Route::get('pos/products', [\App\Http\Controllers\PosController::class, 'searchProducts'])->name('pos.products');
    Route::post('pos/checkout', [\App\Http\Controllers\PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('pos/hold', [\App\Http\Controllers\PosController::class, 'holdTransaction'])->name('pos.hold');
    Route::get('pos/held-list', [\App\Http\Controllers\PosController::class, 'getHeldTransactions'])->name('pos.held-list');
    Route::post('pos/recall/{heldTransaction}', [\App\Http\Controllers\PosController::class, 'recallHeldTransaction'])->name('pos.recall');
    Route::post('pos/void/{sale}', [\App\Http\Controllers\PosController::class, 'voidSale'])->name('pos.void');

    // Cashier Shift Routes
    Route::get('shifts/current', [\App\Http\Controllers\CashierShiftController::class, 'current'])->name('shifts.current');
    Route::post('shifts/open', [\App\Http\Controllers\CashierShiftController::class, 'open'])->name('shifts.open');
    Route::post('shifts/{shift}/close', [\App\Http\Controllers\CashierShiftController::class, 'close'])->name('shifts.close');

    // 3. Master Data Routes
    Route::get('products/{product}/get-price', [\App\Http\Controllers\ProductController::class, 'getPrice'])->name('products.get-price');
    Route::resource('products', ProductController::class)->except(['create', 'show', 'edit']);
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('units', UnitController::class)->except(['create', 'show', 'edit']);
    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class)->except(['create', 'show', 'edit']);
    Route::resource('customers', \App\Http\Controllers\CustomerController::class)->except(['create', 'show', 'edit']);
    Route::resource('customer-groups', \App\Http\Controllers\CustomerGroupController::class)->except(['create', 'show', 'edit']);
    Route::post('warehouses/{warehouse}/set-default', [\App\Http\Controllers\WarehouseController::class, 'setDefault'])->name('warehouses.set-default');
    Route::resource('warehouses', \App\Http\Controllers\WarehouseController::class)->except(['create', 'show', 'edit']);
    Route::resource('discounts', \App\Http\Controllers\DiscountController::class)->except(['create', 'show', 'edit']);

    // 4. User & Role Management
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);

    // 5. User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Official Laravel Breeze Auth Routes
require __DIR__.'/auth.php';
