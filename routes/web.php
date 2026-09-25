<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ExpirationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BatchInventoryController;
use App\Http\Controllers\ExpiredProductDisposalController;
use App\Http\Controllers\InventoryCountController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\BulkProductController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StockReturnController;
use App\Http\Controllers\InventoryLedgerController;
use App\Http\Controllers\SalesController;

// LOGIN
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.submit');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
Route::get('/two-factor', [AuthController::class, 'showTwoFactor'])->name('two-factor.challenge');
Route::post('/two-factor', [AuthController::class, 'verifyTwoFactor'])->middleware('throttle:two-factor')->name('two-factor.verify');

// PROTECTED ROUTES
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/email/verify', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [ProfileController::class, 'update'])->name('settings.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    /*
    |--------------------------------------------------------------------------
    | ADMIN + STAFF
    |--------------------------------------------------------------------------
    */

    Route::get('/product-scan', [ProductController::class, 'scan'])->name('products.scan');
    Route::get('/products/image-match', [ProductController::class, 'imageMatch'])->name('products.image-match');
    Route::get('/products/{product}/label', [ProductController::class, 'label'])->name('products.label');
    Route::get('/products/{product}/placeholder.svg', [ProductController::class, 'placeholder'])->name('products.placeholder');

    Route::middleware('admin')->group(function () {
        Route::resource('products', ProductController::class)
            ->only(['create', 'store', 'edit', 'update', 'destroy']);
    });

    Route::resource('products', ProductController::class)
        ->only(['index', 'show']);

    // STOCK IN
    Route::resource('stock-in', StockInController::class)
        ->only([
            'index',
            'create',
            'store',
        ]);

    // STOCK OUT
    Route::resource('stock-out', StockOutController::class)
        ->only([
            'index',
            'create',
            'store',
        ]);

    // INVENTORY
    Route::get(
        '/inventory',
        [InventoryController::class, 'index']
    )->name('inventory.index');

    Route::get(
        '/batch-inventory',
        [BatchInventoryController::class, 'index']
    )->name('batch-inventory.index');

    // EXPIRATION
    Route::get(
        '/expiration-monitoring',
        [ExpirationController::class, 'index']
    )->name('expiration.index');

    Route::resource('inventory-counts', InventoryCountController::class)
        ->only(['index', 'create', 'store']);
    Route::resource('purchase-orders', PurchaseOrderController::class)
        ->parameters(['purchase-orders' => 'purchaseOrder'])
        ->only(['index','create','store','show']);
    Route::resource('sales', SalesController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/barcode-labels', [BarcodeController::class,'index'])->name('barcodes.index');
    Route::post('/barcode-labels/print', [BarcodeController::class,'print'])->name('barcodes.print');

    Route::middleware('admin')->group(function () {
        Route::post('/sales/{sale}/approve-discount', [SalesController::class, 'approveDiscount'])->name('sales.approve-discount');
        Route::post('/sales/{sale}/return', [SalesController::class, 'returnSale'])->name('sales.return');
        Route::post('/sales/close-day', [SalesController::class, 'closeDay'])->name('sales.close-day');
        Route::get('/inventory-ledger', [InventoryLedgerController::class, 'index'])->name('inventory-ledger.index');
        Route::resource('stock-transfers', StockTransferController::class)->only(['index', 'create', 'store']);
        Route::resource('stock-returns', StockReturnController::class)->only(['index', 'create', 'store']);

        Route::resource('stock-adjustments', StockAdjustmentController::class)
            ->only(['index', 'create', 'store']);

        Route::resource('expired-disposals', ExpiredProductDisposalController::class)
            ->only(['index', 'create', 'store']);

        Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
        Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

        Route::delete(
            '/products/{product}',
            [ProductController::class, 'destroy']
        )->name('products.destroy');

        // CATEGORIES
        Route::resource('categories', CategoryController::class)
            ->except(['show']);

        // SUPPLIERS
        Route::resource('suppliers', SupplierController::class)
            ->except(['show']);

        // REPORTS
        Route::get(
            '/reports',
            [ReportsController::class, 'index']
        )->name('reports.index');

        Route::get('/reports/export', [ReportsController::class, 'export'])
            ->name('reports.export');

        Route::get(
            '/activity-logs',
            [ActivityLogController::class, 'index']
        )->name('activity-logs.index');

        // USERS
        Route::resource(
            'users',
            UserController::class
        )->except(['show']);

        Route::get('/backups/download', [BackupController::class, 'download'])
            ->name('backups.download');
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/{backup}/file', [BackupController::class, 'file'])->name('backups.file');
        Route::post('/backups/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::get('/security/login-history', [SecurityController::class, 'index'])->name('security.index');
        Route::get('/products-bulk', [BulkProductController::class,'form'])->name('products.bulk.form');
        Route::post('/products-bulk/import', [BulkProductController::class,'import'])->name('products.bulk.import');
        Route::get('/products-bulk/template', [BulkProductController::class,'template'])->name('products.bulk.template');
        Route::post('/products-bulk/update', [BulkProductController::class,'update'])->name('products.bulk.update');
    });

    // LOGOUT
    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    )->name('logout');
});
