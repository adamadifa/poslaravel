<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\Api\V1\MasterDataApiController;
use App\Http\Controllers\Api\V1\PosApiController;
use App\Http\Controllers\Api\V1\ShiftApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| POS Laravel REST API V1 Routes
|--------------------------------------------------------------------------
| Mobile Flutter & External API integrations
*/

Route::prefix('v1')->group(function () {
    // Public Auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Authenticated Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        // Auth User
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Dashboard & Transactions
        Route::get('/dashboard/summary', [DashboardApiController::class, 'summary']);
        Route::get('/sales', [DashboardApiController::class, 'sales']);

        // POS Operations
        Route::get('/pos/products', [PosApiController::class, 'searchProducts']);
        Route::get('/pos/products/{product}/get-price', [PosApiController::class, 'getProductPrice']);
        Route::post('/pos/calculate-cart', [PosApiController::class, 'calculateCart']);
        Route::post('/pos/checkout', [PosApiController::class, 'checkout']);
        Route::post('/pos/hold', [PosApiController::class, 'holdTransaction']);
        Route::get('/pos/held-list', [PosApiController::class, 'getHeldTransactions']);
        Route::post('/pos/recall/{id}', [PosApiController::class, 'recallHeldTransaction']);
        Route::post('/pos/void/{sale}', [PosApiController::class, 'voidSale']);

        // Cashier Shift Operations
        Route::get('/shifts/current', [ShiftApiController::class, 'current']);
        Route::post('/shifts/open', [ShiftApiController::class, 'open']);
        Route::post('/shifts/{shift}/close', [ShiftApiController::class, 'close']);
        Route::post('/shifts/{shift}/expenses', [ShiftApiController::class, 'addExpense']);
        Route::delete('/shifts/{shift}/expenses/{expense}', [ShiftApiController::class, 'deleteExpense']);

        // Master Data & Store Settings
        Route::get('/master-summary', [MasterDataApiController::class, 'getMasterSummary']);
        Route::get('/categories', [MasterDataApiController::class, 'getCategories']);
        Route::get('/warehouses', [MasterDataApiController::class, 'getWarehouses']);
        Route::get('/customers', [MasterDataApiController::class, 'getCustomers']);
        Route::post('/customers', [MasterDataApiController::class, 'storeCustomer']);
        Route::get('/customer-groups', [MasterDataApiController::class, 'getCustomerGroups']);
        Route::get('/units', [MasterDataApiController::class, 'getUnits']);
        Route::get('/suppliers', [MasterDataApiController::class, 'getSuppliers']);
        Route::get('/discounts', [MasterDataApiController::class, 'getDiscounts']);
        Route::get('/accounts', [MasterDataApiController::class, 'getAccounts']);
        Route::get('/tables', [MasterDataApiController::class, 'getTables']);
        Route::get('/modifiers', [MasterDataApiController::class, 'getModifierGroups']);
        Route::get('/settings/receipt', [MasterDataApiController::class, 'getReceiptSettings']);
    });
});
