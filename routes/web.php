<?php

use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));
Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

// Public barcode image routes (used in receipts & labels without auth)
Route::get('/barcode/image/{barcode}', [BarcodeController::class, 'image'])->name('barcode.image');
Route::get('/barcode/svg/{barcode}',   [BarcodeController::class, 'svg'])->name('barcode.svg');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── POS + Sales (all roles) ────────────────────────────────────────────────
    Route::get('/pos',                          [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales',                       [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales',                        [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}',                 [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt',         [SaleController::class, 'receipt'])->name('sales.receipt');

    // ── Stock (all roles) ──────────────────────────────────────────────────────
    Route::get('/stock',                        [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/add',                    [StockController::class, 'addStock'])->name('stock.add');
    Route::post('/stock/add',                   [StockController::class, 'processAddStock'])->name('stock.process');

    // ── API endpoints (all roles) ──────────────────────────────────────────────
    Route::get('/api/product-by-barcode',       [ProductController::class, 'getByBarcode'])->name('products.by-barcode');
    Route::get('/api/dashboard/sales-chart',    [DashboardController::class, 'salesChartApi'])->name('dashboard.chart');
    Route::get('/api/products/search-pos',      [ProductController::class, 'searchForPos'])->name('products.search-pos');
    Route::get('/api/stock/barcode',            [StockController::class, 'getProductByBarcode'])->name('stock.by-barcode');
    Route::get('/api/customers/search',         [CustomerController::class, 'search'])->name('customers.search');

    // ── Manager + Admin ────────────────────────────────────────────────────────
    Route::middleware('role:super_admin,admin,manager')->group(function () {

        // Products & Inventory
        Route::resource('products', ProductController::class);
        Route::get('/products/{product}/barcode', [ProductController::class, 'barcodeLabel'])->name('products.barcode');
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('brands', BrandController::class)->except('show');

        // Barcode Management
        Route::get('/barcodes',                [BarcodeController::class, 'index'])->name('barcodes.index');
        Route::get('/barcodes/bulk-generate',  [BarcodeController::class, 'bulkGenerateForm'])->name('barcodes.bulk-generate');
        Route::post('/barcodes/bulk-generate', [BarcodeController::class, 'bulkGenerate'])->name('barcodes.bulk-generate.store');
        Route::get('/barcodes/assign',         [BarcodeController::class, 'assignForm'])->name('barcodes.assign');
        Route::post('/barcodes/assign',        [BarcodeController::class, 'assignBarcode'])->name('barcodes.assign.store');
        Route::post('/barcodes/print-labels',  [BarcodeController::class, 'printLabels'])->name('barcodes.print-labels');
        Route::post('/barcodes/print',         [BarcodeController::class, 'generate'])->name('barcodes.generate');

        // Customers
        Route::resource('customers', CustomerController::class)->only(['index', 'show']);

        // Purchase Management
        Route::resource('suppliers', SupplierController::class);
        Route::resource('purchase-orders', PurchaseOrderController::class);
        Route::resource('grns', GrnController::class)->except(['edit','update','destroy']);
        Route::get('/grns/{grn}', [GrnController::class, 'show'])->name('grns.show');

        // Sales Returns
        Route::get('/sale-returns',              [SaleReturnController::class, 'index'])->name('sale-returns.index');
        Route::get('/sale-returns/create',       [SaleReturnController::class, 'create'])->name('sale-returns.create');
        Route::post('/sale-returns',             [SaleReturnController::class, 'store'])->name('sale-returns.store');
        Route::get('/sale-returns/{saleReturn}', [SaleReturnController::class, 'show'])->name('sale-returns.show');
        Route::get('/api/sale-returns/find',     [SaleReturnController::class, 'findSale'])->name('sale-returns.find');

        // Expenses
        Route::resource('expenses', ExpenseController::class)->except('show');

        // Reports
        Route::get('/reports/daily',             [ReportController::class, 'daily'])->name('reports.daily');
        Route::get('/reports/monthly',           [ReportController::class, 'monthly'])->name('reports.monthly');
        Route::get('/reports/inventory',         [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/profit-loss',       [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
        Route::get('/reports/gst',               [ReportController::class, 'gst'])->name('reports.gst');
        Route::get('/reports/cashier-wise',      [ReportController::class, 'cashierWise'])->name('reports.cashier-wise');
        Route::get('/reports/stock-movement',    [ReportController::class, 'stockMovement'])->name('reports.stock-movement');
    });

    // ── All authenticated users ───────────────────────────────────────────────
    Route::get('/profile',           [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

    // ── Admin + Super Admin only ───────────────────────────────────────────────
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::get('/settings',          [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings',         [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/logo',    [SettingController::class, 'storeLogo'])->name('settings.logo');
    });

    // ── Super Admin only ──────────────────────────────────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/permissions',         [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions/toggle', [PermissionController::class, 'toggle'])->name('permissions.toggle');
        Route::get('/modules',             [ModuleController::class, 'index'])->name('modules.index');
        Route::post('/modules/toggle',     [ModuleController::class, 'toggle'])->name('modules.toggle');
        Route::resource('roles', RoleController::class)->except('show');
    });
});
