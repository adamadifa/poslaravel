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

    // 2. POS Kasir (Full-Screen)
    Route::get('/pos', function () {
        return view('pos.index', [
            'title' => 'Kasir POS'
        ]);
    })->name('pos.index');

    // 3. Master Data Routes
    Route::resource('products', ProductController::class)->only(['index']);
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('units', UnitController::class)->except(['create', 'show', 'edit']);

    // 4. User & Role Management
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);

    // 5. User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Official Laravel Breeze Auth Routes
require __DIR__.'/auth.php';
