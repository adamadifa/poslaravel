<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountTransferController;
use App\Http\Controllers\AgentTransactionController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\CashierShiftController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\ModifierController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PpobProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReceiptController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\ServiceBookingController;
use App\Http\Controllers\ServiceQueueController;
use App\Http\Controllers\ServiceStaffController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Protected Routes (Wajib Login)
Route::middleware(['auth'])->group(function () {

    // 1. Dashboard Utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. POS Routes
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('pos/products', [PosController::class, 'searchProducts'])->name('pos.products');
    Route::post('pos/calculate-cart', [PosController::class, 'calculateCart'])->name('pos.calculate-cart');
    Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('pos/hold', [PosController::class, 'holdTransaction'])->name('pos.hold');
    Route::get('pos/held-list', [PosController::class, 'getHeldTransactions'])->name('pos.held-list');
    Route::post('pos/recall/{heldTransaction}', [PosController::class, 'recallHeldTransaction'])->name('pos.recall');
    Route::post('pos/void/{sale}', [PosController::class, 'voidSale'])->name('pos.void');

    // Cashier Shift Routes
    Route::get('shifts/current', [CashierShiftController::class, 'current'])->name('shifts.current');
    Route::post('shifts/open', [CashierShiftController::class, 'open'])->name('shifts.open');
    Route::post('shifts/{shift}/close', [CashierShiftController::class, 'close'])->name('shifts.close');
    Route::post('shifts/{shift}/expenses', [CashierShiftController::class, 'addExpense'])->name('shifts.expenses.add');
    Route::delete('shifts/{shift}/expenses/{expense}', [CashierShiftController::class, 'deleteExpense'])->name('shifts.expenses.delete');

    // Agent & PPOB Service Routes
    Route::get('agent/balances', [AgentTransactionController::class, 'getBalances'])->name('agent.balances');
    Route::get('agent/recent', [AgentTransactionController::class, 'getRecent'])->name('agent.recent');
    Route::post('agent/transfer', [AgentTransactionController::class, 'storeBankTransfer'])->name('agent.transfer');
    Route::post('agent/withdraw', [AgentTransactionController::class, 'storeCashWithdrawal'])->name('agent.withdraw');
    Route::post('agent/ppob', [AgentTransactionController::class, 'storePpob'])->name('agent.ppob');

    // 3. Master Data Routes
    Route::get('products/barcode-search', [ProductController::class, 'searchForBarcode'])->name('products.barcode-search');
    Route::get('products/{product}/get-price', [ProductController::class, 'getPrice'])->name('products.get-price');
    Route::get('products/{product}/recipes', [RecipeController::class, 'getProductRecipes'])->name('products.recipes');
    Route::post('products/{product}/recipes', [RecipeController::class, 'syncProductRecipes'])->name('products.recipes.sync');
    Route::delete('recipes/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy');
    Route::resource('products', ProductController::class)->except(['create', 'show', 'edit']);
    Route::resource('raw-materials', RawMaterialController::class)->except(['create', 'show', 'edit']);
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('units', UnitController::class)->except(['create', 'show', 'edit']);
    Route::resource('suppliers', SupplierController::class)->except(['create', 'show', 'edit']);
    Route::resource('customers', CustomerController::class)->except(['create', 'show', 'edit']);
    Route::resource('customer-groups', CustomerGroupController::class)->except(['create', 'show', 'edit']);
    Route::post('warehouses/{warehouse}/set-default', [WarehouseController::class, 'setDefault'])->name('warehouses.set-default');
    Route::resource('warehouses', WarehouseController::class)->except(['create', 'show', 'edit']);
    Route::resource('discounts', DiscountController::class)->except(['create', 'show', 'edit']);

    // 3.1 FNB Routes (Table Management, Floor Plan, Modifiers, KDS)
    Route::get('tables/reservations', [DiningTableController::class, 'reservations'])->name('tables.reservations');
    Route::post('tables/reservations', [DiningTableController::class, 'storeReservation'])->name('tables.reservations.store');
    Route::post('tables/reservations/{reservation}/status', [DiningTableController::class, 'updateReservationStatus'])->name('tables.reservations.status');
    Route::post('tables/{table}/status', [DiningTableController::class, 'updateStatus'])->name('tables.status');
    Route::resource('tables', DiningTableController::class)->except(['create', 'show', 'edit']);

    Route::post('modifiers/groups', [ModifierController::class, 'storeGroup'])->name('modifiers.groups.store');
    Route::put('modifiers/groups/{modifierGroup}', [ModifierController::class, 'updateGroup'])->name('modifiers.groups.update');
    Route::delete('modifiers/groups/{modifierGroup}', [ModifierController::class, 'destroyGroup'])->name('modifiers.groups.destroy');
    Route::post('modifiers/groups/{modifierGroup}/sync-products', [ModifierController::class, 'syncProducts'])->name('modifiers.groups.sync-products');
    Route::post('modifiers', [ModifierController::class, 'storeModifier'])->name('modifiers.store');
    Route::put('modifiers/{modifier}', [ModifierController::class, 'updateModifier'])->name('modifiers.update');
    Route::delete('modifiers/{modifier}', [ModifierController::class, 'destroyModifier'])->name('modifiers.destroy');
    Route::get('modifiers', [ModifierController::class, 'index'])->name('modifiers.index');

    Route::get('kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::get('kitchen/orders', [KitchenController::class, 'getActiveOrders'])->name('kitchen.orders');
    Route::post('kitchen/items/{saleItem}/status', [KitchenController::class, 'updateItemStatus'])->name('kitchen.items.status');
    Route::post('kitchen/orders/{sale}/ready', [KitchenController::class, 'markOrderReady'])->name('kitchen.orders.ready');
    Route::post('kitchen/orders/{sale}/bump', [KitchenController::class, 'bumpOrder'])->name('kitchen.orders.bump');

    // 3.2 Service Routes (Staff, Bookings, Service Queue)
    Route::resource('service-staff', ServiceStaffController::class)->only(['index', 'store', 'destroy']);
    Route::get('service-bookings', [ServiceBookingController::class, 'index'])->name('service-bookings.index');
    Route::post('service-bookings', [ServiceBookingController::class, 'store'])->name('service-bookings.store');
    Route::post('service-bookings/{booking}/status', [ServiceBookingController::class, 'updateStatus'])->name('service-bookings.status');
    Route::get('service-queue', [ServiceQueueController::class, 'index'])->name('service-queue.index');
    Route::post('service-queue/{sale}/start', [ServiceQueueController::class, 'start'])->name('service-queue.start');
    Route::post('service-queue/{sale}/complete', [ServiceQueueController::class, 'complete'])->name('service-queue.complete');

    // 4. Purchasing & Procurement Routes (Phase 3)
    Route::get('purchase-orders/{purchaseOrder}/details', [PurchaseOrderController::class, 'getDetails'])->name('purchase-orders.details');
    Route::get('purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
    Route::patch('purchase-orders/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');
    Route::resource('purchase-orders', PurchaseOrderController::class)->except(['create', 'show', 'edit']);
    Route::resource('purchase-receipts', PurchaseReceiptController::class)->except(['create', 'show', 'edit']);
    Route::resource('purchase-returns', PurchaseReturnController::class)->except(['create', 'show', 'edit']);

    // 5. Inventory, Kartu Stok, Opname, Transfer, Adjustment & Alerts (Phase 4)
    Route::get('stocks', [StockMovementController::class, 'index'])->name('stocks.index');
    Route::get('stocks/export-pdf', [StockMovementController::class, 'exportPdf'])->name('stocks.movements.export-pdf');
    Route::get('stocks/export-excel', [StockMovementController::class, 'exportExcel'])->name('stocks.movements.export-excel');
    Route::get('stock-alerts', [StockAlertController::class, 'index'])->name('stocks.alerts');
    Route::post('stock-opnames/{stockOpname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opnames.approve');
    Route::resource('stock-opnames', StockOpnameController::class)->except(['create', 'edit']);
    Route::post('stock-transfers/{stockTransfer}/dispatch', [StockTransferController::class, 'dispatch'])->name('stock-transfers.dispatch');
    Route::post('stock-transfers/{stockTransfer}/receive', [StockTransferController::class, 'receive'])->name('stock-transfers.receive');
    Route::resource('stock-transfers', StockTransferController::class)->except(['create', 'edit']);
    Route::post('stock-adjustments/{stockAdjustment}/approve', [StockAdjustmentController::class, 'approve'])->name('stock-adjustments.approve');
    Route::resource('stock-adjustments', StockAdjustmentController::class)->except(['create', 'edit']);

    // 6. Finance & Kas/Bank (Phase 5)
    Route::post('accounts/{account}/default', [AccountController::class, 'setDefault'])->name('accounts.default');
    Route::get('accounts/{account}/mutations', [AccountController::class, 'mutations'])->name('accounts.mutations');
    Route::resource('accounts', AccountController::class)->except(['create', 'edit']);
    Route::get('api/ppob-products', [PpobProductController::class, 'apiList'])->name('api.ppob-products');
    Route::resource('ppob-products', PpobProductController::class)->except(['create', 'edit']);
    Route::get('payables', [PaymentController::class, 'payables'])->name('payables.index');
    Route::post('payables', [PaymentController::class, 'storePayable'])->name('payables.store');
    Route::get('receivables', [PaymentController::class, 'receivables'])->name('receivables.index');
    Route::post('receivables', [PaymentController::class, 'storeReceivable'])->name('receivables.store');
    Route::resource('cash-flows', CashFlowController::class)->except(['create', 'edit']);
    Route::resource('account-transfers', AccountTransferController::class)->only(['index', 'store']);

    // 7. Penjualan & Retur Penjualan
    Route::resource('sales', SaleController::class)->only(['index', 'show']);
    Route::get('sale-returns/search-invoice', [SaleReturnController::class, 'searchInvoice'])->name('sale-returns.search-invoice');
    Route::get('sale-returns/list-invoices', [SaleReturnController::class, 'listInvoices'])->name('sale-returns.list-invoices');
    Route::resource('sale-returns', SaleReturnController::class)->only(['index', 'store', 'destroy']);

    // 8. User & Role Management
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
    Route::resource('roles', RoleController::class)->except(['create', 'show', 'edit']);

    // 9. Laporan & Analytics (Phase 6)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/sales/products', [ReportController::class, 'salesByProduct'])->name('sales.products');
        Route::get('/sales/products/export-pdf', [ReportController::class, 'exportSalesByProductPdf'])->name('sales.products.export-pdf');
        Route::get('/sales/products/export-excel', [ReportController::class, 'exportSalesByProductExcel'])->name('sales.products.export-excel');
        Route::get('/sales/categories', [ReportController::class, 'salesByCategory'])->name('sales.categories');
        Route::get('/sales/categories/export-pdf', [ReportController::class, 'exportSalesByCategoryPdf'])->name('sales.categories.export-pdf');
        Route::get('/sales/categories/export-excel', [ReportController::class, 'exportSalesByCategoryExcel'])->name('sales.categories.export-excel');

        Route::get('/sales/customers', [ReportController::class, 'salesByCustomer'])->name('sales.customers');
        Route::get('/sales/customers/export-pdf', [ReportController::class, 'exportSalesByCustomerPdf'])->name('sales.customers.export-pdf');
        Route::get('/sales/customers/export-excel', [ReportController::class, 'exportSalesByCustomerExcel'])->name('sales.customers.export-excel');

        Route::get('/sales/export-pdf', [ReportController::class, 'exportSalesPdf'])->name('sales.export-pdf');
        Route::get('/sales/export-excel', [ReportController::class, 'exportSalesExcel'])->name('sales.export-excel');

        // 6.2 Laporan Pembelian
        Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('/purchases/export-pdf', [ReportController::class, 'exportPurchasesPdf'])->name('purchases.export-pdf');
        Route::get('/purchases/export-excel', [ReportController::class, 'exportPurchasesExcel'])->name('purchases.export-excel');

        // 6.3 Laporan Stok & Inventori
        Route::get('/stocks', [ReportController::class, 'stocks'])->name('stocks');
        Route::get('/stocks/export-pdf', [ReportController::class, 'exportStocksPdf'])->name('stocks.export-pdf');
        Route::get('/stocks/export-excel', [ReportController::class, 'exportStocksExcel'])->name('stocks.export-excel');

        Route::get('/stock-opnames', [ReportController::class, 'stockOpnames'])->name('stock-opnames');
        Route::get('/stock-opnames/export-pdf', [ReportController::class, 'exportStockOpnamesPdf'])->name('stock-opnames.export-pdf');
        Route::get('/stock-opnames/export-excel', [ReportController::class, 'exportStockOpnamesExcel'])->name('stock-opnames.export-excel');

        // 6.4 Laporan Keuangan (Finance)
        Route::get('/payables', [ReportController::class, 'payables'])->name('payables');
        Route::get('/payables/export-pdf', [ReportController::class, 'exportPayablesPdf'])->name('payables.export-pdf');
        Route::get('/payables/export-excel', [ReportController::class, 'exportPayablesExcel'])->name('payables.export-excel');

        Route::get('/receivables', [ReportController::class, 'receivables'])->name('receivables');
        Route::get('/receivables/export-pdf', [ReportController::class, 'exportReceivablesPdf'])->name('receivables.export-pdf');
        Route::get('/receivables/export-excel', [ReportController::class, 'exportReceivablesExcel'])->name('receivables.export-excel');

        Route::get('/cash-flows', [ReportController::class, 'cashFlows'])->name('cash-flows');
        Route::get('/cash-flows/export-pdf', [ReportController::class, 'exportCashFlowsPdf'])->name('cash-flows.export-pdf');
        Route::get('/cash-flows/export-excel', [ReportController::class, 'exportCashFlowsExcel'])->name('cash-flows.export-excel');

        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/profit-loss/export-pdf', [ReportController::class, 'exportProfitLossPdf'])->name('profit-loss.export-pdf');
        Route::get('/profit-loss/export-excel', [ReportController::class, 'exportProfitLossExcel'])->name('profit-loss.export-excel');

        Route::get('/cashier-shifts', [ReportController::class, 'cashierShifts'])->name('cashier-shifts');
        Route::get('/cashier-shifts/export-pdf', [ReportController::class, 'exportCashierShiftsPdf'])->name('cashier-shifts.export-pdf');
        Route::get('/cashier-shifts/export-excel', [ReportController::class, 'exportCashierShiftsExcel'])->name('cashier-shifts.export-excel');
    });

    // 10. Pengaturan Toko & Konfigurasi (Phase 6.6)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/profile', [SettingController::class, 'updateProfile'])->name('profile');
        Route::post('/business-type', [SettingController::class, 'updateBusinessType'])->name('business-type');
        Route::post('/prefixes', [SettingController::class, 'updatePrefixes'])->name('prefixes');
        Route::post('/tax', [SettingController::class, 'updateTaxCurrency'])->name('tax');
        Route::post('/receipt', [SettingController::class, 'updateReceipt'])->name('receipt');
        Route::post('/agent', [SettingController::class, 'updateAgent'])->name('agent');
    });

    // 11. Audit Trail (Phase 6.7)
    Route::get('/audit-trails', [AuditTrailController::class, 'index'])->name('audit-trails.index');
    Route::get('/audit-trails/{auditTrail}', [AuditTrailController::class, 'show'])->name('audit-trails.show');

    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Official Laravel Breeze Auth Routes
require __DIR__.'/auth.php';
