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
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

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

    // 4. Purchasing & Procurement Routes (Phase 3)
    Route::get('purchase-orders/{purchaseOrder}/details', [\App\Http\Controllers\PurchaseOrderController::class, 'getDetails'])->name('purchase-orders.details');
    Route::patch('purchase-orders/{purchaseOrder}/status', [\App\Http\Controllers\PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');
    Route::resource('purchase-orders', \App\Http\Controllers\PurchaseOrderController::class)->except(['create', 'show', 'edit']);
    Route::resource('purchase-receipts', \App\Http\Controllers\PurchaseReceiptController::class)->except(['create', 'show', 'edit']);
    Route::resource('purchase-returns', \App\Http\Controllers\PurchaseReturnController::class)->except(['create', 'show', 'edit']);

    // 5. Inventory, Kartu Stok, Opname, Transfer, Adjustment & Alerts (Phase 4)
    Route::get('stocks', [\App\Http\Controllers\StockMovementController::class, 'index'])->name('stocks.index');
    Route::get('stock-alerts', [\App\Http\Controllers\StockAlertController::class, 'index'])->name('stocks.alerts');
    Route::post('stock-opnames/{stockOpname}/approve', [\App\Http\Controllers\StockOpnameController::class, 'approve'])->name('stock-opnames.approve');
    Route::resource('stock-opnames', \App\Http\Controllers\StockOpnameController::class)->except(['create', 'edit']);
    Route::post('stock-transfers/{stockTransfer}/dispatch', [\App\Http\Controllers\StockTransferController::class, 'dispatch'])->name('stock-transfers.dispatch');
    Route::post('stock-transfers/{stockTransfer}/receive', [\App\Http\Controllers\StockTransferController::class, 'receive'])->name('stock-transfers.receive');
    Route::resource('stock-transfers', \App\Http\Controllers\StockTransferController::class)->except(['create', 'edit']);
    Route::post('stock-adjustments/{stockAdjustment}/approve', [\App\Http\Controllers\StockAdjustmentController::class, 'approve'])->name('stock-adjustments.approve');
    Route::resource('stock-adjustments', \App\Http\Controllers\StockAdjustmentController::class)->except(['create', 'edit']);

    // 6. Finance & Kas/Bank (Phase 5)
    Route::post('accounts/{account}/default', [\App\Http\Controllers\AccountController::class, 'setDefault'])->name('accounts.default');
    Route::resource('accounts', \App\Http\Controllers\AccountController::class)->except(['create', 'edit']);
    Route::get('payables', [\App\Http\Controllers\PaymentController::class, 'payables'])->name('payables.index');
    Route::post('payables', [\App\Http\Controllers\PaymentController::class, 'storePayable'])->name('payables.store');
    Route::get('receivables', [\App\Http\Controllers\PaymentController::class, 'receivables'])->name('receivables.index');
    Route::post('receivables', [\App\Http\Controllers\PaymentController::class, 'storeReceivable'])->name('receivables.store');
    Route::resource('cash-flows', \App\Http\Controllers\CashFlowController::class)->only(['index', 'store']);
    Route::resource('account-transfers', \App\Http\Controllers\AccountTransferController::class)->only(['index', 'store']);

    // 7. Retur Penjualan (Phase 5.6)
    Route::get('sale-returns/search-invoice', [\App\Http\Controllers\SaleReturnController::class, 'searchInvoice'])->name('sale-returns.search-invoice');
    Route::get('sale-returns/list-invoices', [\App\Http\Controllers\SaleReturnController::class, 'listInvoices'])->name('sale-returns.list-invoices');
    Route::resource('sale-returns', \App\Http\Controllers\SaleReturnController::class)->only(['index', 'store', 'destroy']);

    // 8. User & Role Management
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);

    // 9. Laporan & Analytics (Phase 6)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::get('/sales', [\App\Http\Controllers\ReportController::class, 'sales'])->name('sales');
        Route::get('/sales/products', [\App\Http\Controllers\ReportController::class, 'salesByProduct'])->name('sales.products');
        Route::get('/sales/categories', [\App\Http\Controllers\ReportController::class, 'salesByCategory'])->name('sales.categories');
        Route::get('/sales/customers', [\App\Http\Controllers\ReportController::class, 'salesByCustomer'])->name('sales.customers');
        Route::get('/sales/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportSalesPdf'])->name('sales.export-pdf');
        Route::get('/sales/export-excel', [\App\Http\Controllers\ReportController::class, 'exportSalesExcel'])->name('sales.export-excel');
        
        // 6.2 Laporan Pembelian
        Route::get('/purchases', [\App\Http\Controllers\ReportController::class, 'purchases'])->name('purchases');
        Route::get('/purchases/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportPurchasesPdf'])->name('purchases.export-pdf');
        Route::get('/purchases/export-excel', [\App\Http\Controllers\ReportController::class, 'exportPurchasesExcel'])->name('purchases.export-excel');

        // 6.3 Laporan Stok & Inventori
        Route::get('/stocks', [\App\Http\Controllers\ReportController::class, 'stocks'])->name('stocks');
        Route::get('/stock-opnames', [\App\Http\Controllers\ReportController::class, 'stockOpnames'])->name('stock-opnames');
        Route::get('/stocks/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportStocksPdf'])->name('stocks.export-pdf');
        Route::get('/stocks/export-excel', [\App\Http\Controllers\ReportController::class, 'exportStocksExcel'])->name('stocks.export-excel');

        // 6.4 Laporan Keuangan (Finance)
        Route::get('/payables', [\App\Http\Controllers\ReportController::class, 'payables'])->name('payables');
        Route::get('/receivables', [\App\Http\Controllers\ReportController::class, 'receivables'])->name('receivables');
        Route::get('/cash-flows', [\App\Http\Controllers\ReportController::class, 'cashFlows'])->name('cash-flows');
        Route::get('/profit-loss', [\App\Http\Controllers\ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/cashier-shifts', [\App\Http\Controllers\ReportController::class, 'cashierShifts'])->name('cashier-shifts');
    });

    // 10. Pengaturan Toko & Konfigurasi (Phase 6.6)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SettingController::class, 'index'])->name('index');
        Route::post('/profile', [\App\Http\Controllers\SettingController::class, 'updateProfile'])->name('profile');
        Route::post('/prefixes', [\App\Http\Controllers\SettingController::class, 'updatePrefixes'])->name('prefixes');
        Route::post('/tax', [\App\Http\Controllers\SettingController::class, 'updateTaxCurrency'])->name('tax');
        Route::post('/receipt', [\App\Http\Controllers\SettingController::class, 'updateReceipt'])->name('receipt');
    });

    // 11. Audit Trail (Phase 6.7)
    Route::get('/audit-trails', [\App\Http\Controllers\AuditTrailController::class, 'index'])->name('audit-trails.index');
    Route::get('/audit-trails/{auditTrail}', [\App\Http\Controllers\AuditTrailController::class, 'show'])->name('audit-trails.show');

    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Official Laravel Breeze Auth Routes
require __DIR__.'/auth.php';
