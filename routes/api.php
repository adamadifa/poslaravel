<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\Api\V1\FinanceApiController;
use App\Http\Controllers\Api\V1\MasterDataApiController;
use App\Http\Controllers\Api\V1\PosApiController;
use App\Http\Controllers\Api\V1\ProductApiController;
use App\Http\Controllers\Api\V1\PurchasingApiController;
use App\Http\Controllers\Api\V1\ReportApiController;
use App\Http\Controllers\Api\V1\SaleReturnApiController;
use App\Http\Controllers\Api\V1\SettingApiController;
use App\Http\Controllers\Api\V1\ShiftApiController;
use App\Http\Controllers\Api\V1\StockApiController;
use App\Http\Controllers\Api\V1\UserApiController;
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

        // Retur Penjualan (Sales Returns)
        Route::get('/sales-returns', [SaleReturnApiController::class, 'getReturns']);
        Route::get('/sales-returns/invoices', [SaleReturnApiController::class, 'searchInvoices']);
        Route::get('/sales-returns/{saleReturn}', [SaleReturnApiController::class, 'showReturn']);
        Route::post('/sales-returns', [SaleReturnApiController::class, 'storeReturn']);
        Route::delete('/sales-returns/{saleReturn}', [SaleReturnApiController::class, 'destroyReturn']);

        // POS Operations
        Route::get('/pos/products', [PosApiController::class, 'searchProducts']);
        Route::get('/pos/products/{product}/get-price', [PosApiController::class, 'getProductPrice']);
        Route::post('/pos/calculate-cart', [PosApiController::class, 'calculateCart']);
        Route::post('/pos/checkout', [PosApiController::class, 'checkout']);
        Route::post('/pos/hold', [PosApiController::class, 'holdTransaction']);
        Route::get('/pos/held-list', [PosApiController::class, 'getHeldTransactions']);
        Route::post('/pos/recall/{id}', [PosApiController::class, 'recallHeldTransaction']);
        Route::post('/pos/void/{sale}', [PosApiController::class, 'voidSale']);
        Route::get('/pos/receivables', [PosApiController::class, 'getReceivables']);
        Route::post('/pos/receivables/payment', [PosApiController::class, 'storeReceivablePayment']);
        Route::get('/pos/sales/{sale}/payments', [PosApiController::class, 'getReceivablePayments']);

        // Cashier Shift Operations
        Route::get('/shifts/current', [ShiftApiController::class, 'current']);
        Route::post('/shifts/open', [ShiftApiController::class, 'open']);
        Route::post('/shifts/{shift}/close', [ShiftApiController::class, 'close']);
        Route::post('/shifts/{shift}/expenses', [ShiftApiController::class, 'addExpense']);
        Route::delete('/shifts/{shift}/expenses/{expense}', [ShiftApiController::class, 'deleteExpense']);

        // Buku Arus Kas Masuk & Kas Keluar (Cash Flow)
        Route::get('/cash-flows', [FinanceApiController::class, 'getCashFlows']);
        Route::get('/cash-flows/categories', [FinanceApiController::class, 'getCategories']);
        Route::get('/cash-flows/{cashFlow}', [FinanceApiController::class, 'showCashFlow']);
        Route::post('/cash-flows', [FinanceApiController::class, 'storeCashFlow']);
        Route::match(['put', 'post'], '/cash-flows/{cashFlow}', [FinanceApiController::class, 'updateCashFlow']);
        Route::delete('/cash-flows/{cashFlow}', [FinanceApiController::class, 'destroyCashFlow']);

        // Transfer Antar Kas & Bank (Account Transfers)
        Route::get('/account-transfers', [FinanceApiController::class, 'getAccountTransfers']);
        Route::get('/account-transfers/{accountTransfer}', [FinanceApiController::class, 'showAccountTransfer']);
        Route::post('/account-transfers', [FinanceApiController::class, 'storeAccountTransfer']);
        Route::delete('/account-transfers/{accountTransfer}', [FinanceApiController::class, 'destroyAccountTransfer']);

        // Master Data & Store Settings
        Route::get('/master-summary', [MasterDataApiController::class, 'getMasterSummary']);
        Route::post('/products', [ProductApiController::class, 'store']);
        Route::match(['put', 'post'], '/products/{product}', [ProductApiController::class, 'update']);
        Route::delete('/products/{product}', [ProductApiController::class, 'destroy']);
        Route::get('/categories', [MasterDataApiController::class, 'getCategories']);
        Route::post('/categories', [MasterDataApiController::class, 'storeCategory']);
        Route::match(['put', 'post'], '/categories/{category}', [MasterDataApiController::class, 'updateCategory']);
        Route::delete('/categories/{category}', [MasterDataApiController::class, 'destroyCategory']);
        Route::get('/warehouses', [MasterDataApiController::class, 'getWarehouses']);
        Route::post('/warehouses', [MasterDataApiController::class, 'storeWarehouse']);
        Route::match(['put', 'post'], '/warehouses/{warehouse}', [MasterDataApiController::class, 'updateWarehouse']);
        Route::delete('/warehouses/{warehouse}', [MasterDataApiController::class, 'destroyWarehouse']);
        Route::get('/customers', [MasterDataApiController::class, 'getCustomers']);
        Route::post('/customers', [MasterDataApiController::class, 'storeCustomer']);
        Route::match(['put', 'post'], '/customers/{customer}', [MasterDataApiController::class, 'updateCustomer']);
        Route::delete('/customers/{customer}', [MasterDataApiController::class, 'destroyCustomer']);
        Route::get('/customer-groups', [MasterDataApiController::class, 'getCustomerGroups']);
        Route::get('/units', [MasterDataApiController::class, 'getUnits']);
        Route::post('/units', [MasterDataApiController::class, 'storeUnit']);
        Route::match(['put', 'post'], '/units/{unit}', [MasterDataApiController::class, 'updateUnit']);
        Route::delete('/units/{unit}', [MasterDataApiController::class, 'destroyUnit']);
        Route::get('/suppliers', [MasterDataApiController::class, 'getSuppliers']);
        Route::post('/suppliers', [MasterDataApiController::class, 'storeSupplier']);
        Route::match(['put', 'post'], '/suppliers/{supplier}', [MasterDataApiController::class, 'updateSupplier']);
        Route::delete('/suppliers/{supplier}', [MasterDataApiController::class, 'destroySupplier']);
        Route::get('/discounts', [MasterDataApiController::class, 'getDiscounts']);
        Route::post('/discounts', [MasterDataApiController::class, 'storeDiscount']);
        Route::match(['put', 'post'], '/discounts/{discount}', [MasterDataApiController::class, 'updateDiscount']);
        Route::get('/accounts', [MasterDataApiController::class, 'getAccounts']);
        Route::post('/accounts', [MasterDataApiController::class, 'storeAccount']);
        Route::match(['put', 'post'], '/accounts/{account}', [MasterDataApiController::class, 'updateAccount']);
        Route::delete('/accounts/{account}', [MasterDataApiController::class, 'destroyAccount']);
        Route::get('/accounts/{account}/mutations', [MasterDataApiController::class, 'getAccountMutations']);
        Route::get('/ppob-products', [MasterDataApiController::class, 'getPpobProducts']);
        Route::post('/ppob-products', [MasterDataApiController::class, 'storePpobProduct']);
        Route::get('/ppob-products/{ppobProduct}', [MasterDataApiController::class, 'showPpobProduct']);
        Route::match(['put', 'post'], '/ppob-products/{ppobProduct}', [MasterDataApiController::class, 'updatePpobProduct']);
        Route::delete('/ppob-products/{ppobProduct}', [MasterDataApiController::class, 'destroyPpobProduct']);
        Route::get('/tables', [MasterDataApiController::class, 'getTables']);
        Route::post('/tables', [MasterDataApiController::class, 'storeTable']);
        Route::match(['put', 'post'], '/tables/{table}', [MasterDataApiController::class, 'updateTable']);
        Route::delete('/tables/{table}', [MasterDataApiController::class, 'destroyTable']);
        Route::get('/modifiers', [MasterDataApiController::class, 'getModifierGroups']);
        Route::get('/settings/receipt', [MasterDataApiController::class, 'getReceiptSettings']);

        // Purchasing & Pengadaan
        Route::get('/purchases/orders', [PurchasingApiController::class, 'getOrders']);
        Route::get('/purchases/orders/{purchaseOrder}', [PurchasingApiController::class, 'showOrder']);
        Route::post('/purchases/orders', [PurchasingApiController::class, 'storeOrder']);
        Route::match(['put', 'post'], '/purchases/orders/{purchaseOrder}', [PurchasingApiController::class, 'updateOrder']);
        Route::post('/purchases/orders/{purchaseOrder}/status', [PurchasingApiController::class, 'updateOrderStatus']);
        Route::delete('/purchases/orders/{purchaseOrder}', [PurchasingApiController::class, 'destroyOrder']);
        Route::get('/purchases/receipts', [PurchasingApiController::class, 'getReceipts']);
        Route::post('/purchases/receipts', [PurchasingApiController::class, 'storeReceipt']);
        Route::get('/purchases/returns', [PurchasingApiController::class, 'getReturns']);
        Route::get('/purchases/returns/{purchaseReturn}', [PurchasingApiController::class, 'showReturn']);
        Route::post('/purchases/returns', [PurchasingApiController::class, 'storeReturn']);
        Route::delete('/purchases/returns/{purchaseReturn}', [PurchasingApiController::class, 'destroyReturn']);
        Route::get('/purchases/payables', [PurchasingApiController::class, 'getPayables']);
        Route::post('/purchases/payables/payment', [PurchasingApiController::class, 'storePayablePayment']);
        Route::get('/purchases/receipts/{purchaseReceipt}/payments', [PurchasingApiController::class, 'getPayablePayments']);
        Route::delete('/purchases/payments/{payment}', [PurchasingApiController::class, 'destroyPayment']);

        // Inventaris & Kartu Stok (FIFO) & Peringatan Stok
        Route::get('/stocks/alerts', [StockApiController::class, 'getAlerts']);
        Route::get('/stocks/movements', [StockApiController::class, 'getMovements']);
        Route::get('/stocks/batches', [StockApiController::class, 'getBatches']);
        Route::get('/stocks/products/{product}', [StockApiController::class, 'getProductStockCard']);

        // Stok Opname & Audit Inventaris
        Route::get('/stocks/opnames', [StockApiController::class, 'getOpnames']);
        Route::get('/stocks/opnames/{stockOpname}', [StockApiController::class, 'getOpnameDetail']);
        Route::post('/stocks/opnames', [StockApiController::class, 'storeOpname']);
        Route::put('/stocks/opnames/{stockOpname}', [StockApiController::class, 'updateOpname']);
        Route::post('/stocks/opnames/{stockOpname}/approve', [StockApiController::class, 'approveOpname']);
        Route::delete('/stocks/opnames/{stockOpname}', [StockApiController::class, 'destroyOpname']);

        // Transfer Antar Gudang
        Route::get('/stocks/transfers', [StockApiController::class, 'getTransfers']);
        Route::get('/stocks/transfers/{stockTransfer}', [StockApiController::class, 'getTransferDetail']);
        Route::post('/stocks/transfers', [StockApiController::class, 'storeTransfer']);
        Route::post('/stocks/transfers/{stockTransfer}/dispatch', [StockApiController::class, 'dispatchTransfer']);
        Route::post('/stocks/transfers/{stockTransfer}/receive', [StockApiController::class, 'receiveTransfer']);
        Route::delete('/stocks/transfers/{stockTransfer}', [StockApiController::class, 'destroyTransfer']);

        // Penyesuaian Stok (Stock Adjustments)
        Route::get('/stocks/adjustments', [StockApiController::class, 'getAdjustments']);
        Route::get('/stocks/adjustments/{stockAdjustment}', [StockApiController::class, 'getAdjustmentDetail']);
        Route::post('/stocks/adjustments', [StockApiController::class, 'storeAdjustment']);
        Route::put('/stocks/adjustments/{stockAdjustment}', [StockApiController::class, 'updateAdjustment']);
        Route::post('/stocks/adjustments/{stockAdjustment}/approve', [StockApiController::class, 'approveAdjustment']);
        Route::delete('/stocks/adjustments/{stockAdjustment}', [StockApiController::class, 'destroyAdjustment']);

        // Laporan & Analitik Bisnis (Reports & Analytics)
        Route::prefix('reports')->group(function () {
            Route::get('/sales-summary', [ReportApiController::class, 'getSalesSummary']);
            Route::get('/sales-by-product', [ReportApiController::class, 'getSalesByProduct']);
            Route::get('/sales-by-category', [ReportApiController::class, 'getSalesByCategory']);
            Route::get('/sales-by-customer', [ReportApiController::class, 'getSalesByCustomer']);
            Route::get('/purchases', [ReportApiController::class, 'getPurchases']);
            Route::get('/profit-loss', [ReportApiController::class, 'getProfitLoss']);
            Route::get('/inventory-valuation', [ReportApiController::class, 'getInventoryValuation']);
            Route::get('/stock-opnames', [ReportApiController::class, 'getStockOpnames']);
            Route::get('/payables', [ReportApiController::class, 'getPayables']);
            Route::get('/receivables', [ReportApiController::class, 'getReceivables']);
            Route::get('/cash-flows', [ReportApiController::class, 'getCashFlows']);
            Route::get('/cashier-shifts', [ReportApiController::class, 'getCashierShifts']);
        });

        // Staf, Pengguna & Hak Akses (Users & Roles)
        Route::get('/users', [UserApiController::class, 'getUsers']);
        Route::post('/users', [UserApiController::class, 'storeUser']);
        Route::get('/users/{user}', [UserApiController::class, 'showUser']);
        Route::match(['put', 'post'], '/users/{user}', [UserApiController::class, 'updateUser']);
        Route::delete('/users/{user}', [UserApiController::class, 'destroyUser']);

        Route::get('/roles', [UserApiController::class, 'getRoles']);
        Route::post('/roles', [UserApiController::class, 'storeRole']);
        Route::match(['put', 'post'], '/roles/{role}', [UserApiController::class, 'updateRole']);
        Route::delete('/roles/{role}', [UserApiController::class, 'destroyRole']);

        // Pengaturan Sistem & Toko (Store Settings)
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingApiController::class, 'getSettings']);
            Route::post('/profile', [SettingApiController::class, 'updateProfile']);
            Route::post('/business-type', [SettingApiController::class, 'updateBusinessType']);
            Route::post('/prefixes', [SettingApiController::class, 'updatePrefixes']);
            Route::post('/tax-currency', [SettingApiController::class, 'updateTaxCurrency']);
            Route::post('/receipt', [SettingApiController::class, 'updateReceipt']);
            Route::post('/agent', [SettingApiController::class, 'updateAgent']);
        });
    });
});
