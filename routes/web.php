<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminShoppingAccessCodeController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\InternalShoppingAccessController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminMedicineController;
use App\Http\Controllers\AdminMedicineImportController;
use App\Http\Controllers\AdminPrescriptionController;
use App\Http\Controllers\AdminPrescriptionImportController;
use App\Http\Controllers\AdminPrescriptionProductController;
use App\Http\Controllers\AdminPrescriptionProductImportController;
use App\Http\Controllers\AdminProdukController;
use App\Http\Controllers\AdminProdukImportController;
use App\Http\Controllers\AdminBusinessDataExportController;
use App\Http\Controllers\AdminBannerController;
use App\Http\Controllers\AdminMedicinePromotionController;
use App\Http\Controllers\AdminDiscountCodeController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\GassRoomController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang-kami', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// Serve uploaded images langsung dari storage/ (banners, promos & medicines)
// Dipakai saat symlink tidak tersedia di hosting
Route::get('/storage/{folder}/{filename}', function (string $folder, string $filename) {
    $allowed = ['banners', 'medicines', 'promos'];
    if (!in_array($folder, $allowed)) abort(404);

    $path = storage_path($folder . '/' . $filename);
    if (!file_exists($path)) abort(404);

    $mime = mime_content_type($path) ?: 'application/octet-stream';
    return response()->file($path, ['Content-Type' => $mime]);
})->where(['folder' => 'banners|medicines|promos', 'filename' => '.+'])->name('storage.image');

// Products routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/stock-availability', [ProductController::class, 'stockAvailability'])->name('products.stock-availability');
Route::get('/belanja-internal/akses', [InternalShoppingAccessController::class, 'show'])->name('shopping.internal.access');
Route::post('/belanja-internal/akses', [InternalShoppingAccessController::class, 'verify'])
    ->middleware('throttle:5,1')
    ->name('shopping.internal.verify');
Route::post('/belanja-internal/keluar', [InternalShoppingAccessController::class, 'logout'])->name('shopping.internal.logout');
Route::get('/belanja-internal', [ProductController::class, 'index'])
    ->middleware('internal.access')
    ->name('shopping.internal');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products-pbf', [ProductController::class, 'pbf'])->name('products.pbf');
Route::post('/products-pbf/verify', [ProductController::class, 'pbfVerify'])->middleware('throttle:5,1')->name('products.pbf.verify');
Route::post('/products-pbf/logout', [ProductController::class, 'pbfLogout'])->name('products.pbf.logout');
Route::get('/products-apotek', [ProductController::class, 'apotek'])->name('products.apotek');
Route::post('/products-apotek/activate', [ProductController::class, 'apotekActivate'])
    ->middleware('throttle:5,1')
    ->name('products.apotek.activate');
Route::get('/keranjang', [ProductController::class, 'cart'])->name('cart.index');
Route::get('/cart', [ProductController::class, 'cart'])->name('cart.page');
Route::get('/checkout', [ProductController::class, 'checkout'])->name('checkout.index');
Route::post('/checkout/validate-discount', [PurchaseHistoryController::class, 'validateDiscountCode'])->name('checkout.validate-discount');
Route::post('/orders/history', [PurchaseHistoryController::class, 'store'])->name('orders.history.store');
Route::get('/invoice/{order}', [PurchaseHistoryController::class, 'invoice'])
    ->middleware('signed')
    ->name('orders.invoice');

// Category routes (Layer 2 & 3)
Route::get('/category/{main}/{sub}', [CategoryController::class, 'layer2'])->name('category.layer2');

// Medicine detail (public)
Route::get('/medicines/{id}', [HomeController::class, 'show'])->name('medicines.show');

// Prescriptions routes
Route::get('/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
Route::get('/prescriptions/{id}', [PrescriptionController::class, 'show'])->name('prescriptions.show');

// Auth routes
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/customer/locations/search', [CustomerAccountController::class, 'searchLocations'])->middleware(['auth', 'throttle:10,1'])->name('customer.locations.search');
Route::get('/customer/account', [CustomerAccountController::class, 'showProfile'])->name('customer.account');
Route::put('/customer/account', [CustomerAccountController::class, 'updateProfile'])->name('customer.account.update');
Route::post('/customer/logout', [CustomerAccountController::class, 'logout'])->name('customer.logout');
Route::get('/cashier/login', [CashierController::class, 'showLogin'])->name('cashier.login');
Route::post('/cashier/login', [CashierController::class, 'login'])->middleware('throttle:10,1')->name('cashier.login.post');
Route::post('/cashier/logout', [CashierController::class, 'logout'])->middleware(['auth', 'cashier'])->name('cashier.logout');

Route::middleware(['auth', 'staff'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/dashboard', [CashierController::class, 'dashboard'])->name('dashboard');
    Route::get('/updates', [CashierController::class, 'updates'])->name('updates');
    Route::get('/orders/{order}/receipt', [CashierController::class, 'receipt'])->name('orders.receipt');
    Route::post('/orders/{order}/verify', [CashierController::class, 'verifyAndPrint'])->name('orders.verify');
    Route::post('/orders/{order}/printed', [CashierController::class, 'markPrinted'])->name('orders.printed');
    Route::post('/orders/{order}/cancel', [CashierController::class, 'cancel'])->name('orders.cancel');
});

// GASS shared file room
Route::get('/gass/access', [GassRoomController::class, 'accessForm'])->name('gass.access.form');
Route::get('/gass/room', [GassRoomController::class, 'index'])->name('gass.room');
Route::post('/gass/access', [GassRoomController::class, 'access'])
    ->middleware('throttle:5,1')
    ->name('gass.access');
Route::post('/gass/room/files', [GassRoomController::class, 'upload'])->name('gass.files.upload');
Route::post('/gass/room/files/{file}/share', [GassRoomController::class, 'toggleSharing'])->name('gass.files.share');
Route::get('/gass/shared/{token}', [GassRoomController::class, 'sharedView'])->name('gass.files.shared.view');
Route::get('/gass/shared/{token}/content', [GassRoomController::class, 'sharedContent'])->name('gass.files.shared.content');
Route::get('/gass/shared/{token}/download', [GassRoomController::class, 'sharedDownload'])->name('gass.files.shared.download');
Route::get('/gass/room/files/{file}/view', [GassRoomController::class, 'viewFile'])->name('gass.files.view');
Route::get('/gass/room/files/{file}/content', [GassRoomController::class, 'fileContent'])->name('gass.files.content');
Route::delete('/gass/room/files/{file}', [GassRoomController::class, 'destroy'])->name('gass.files.destroy');
Route::get('/gass/room/files/{file}', [GassRoomController::class, 'download'])->name('gass.files.download');
Route::post('/gass/leave', [GassRoomController::class, 'leave'])->name('gass.leave');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/purchase-history', [AdminDashboardController::class, 'purchaseHistory'])->name('purchase-history.index');
    Route::get('/purchase-history/{order}/purchase-order', [PurchaseHistoryController::class, 'purchaseOrder'])->name('purchase-history.purchase-order');
    Route::get('/purchase-history/export', [AdminDashboardController::class, 'exportPurchaseHistory'])->name('purchase-history.export');
    Route::post('/purchase-history/{order}/approval', [AdminDashboardController::class, 'updateApprovalStatus'])->name('purchase-history.approval');
    Route::delete('/purchase-history/{order}', [AdminDashboardController::class, 'destroy'])->name('purchase-history.destroy');
    Route::delete('/purchase-history', [AdminDashboardController::class, 'destroyAll'])->name('purchase-history.destroyAll');
    Route::get('/customers/export', [AdminCustomerController::class, 'export'])->name('customers.export');
    Route::resource('customers', AdminCustomerController::class)->except('show');
    Route::get('/access-codes', [AdminShoppingAccessCodeController::class, 'index'])->name('access-codes.index');
    Route::post('/access-codes/{channel}/generate', [AdminShoppingAccessCodeController::class, 'generate'])
        ->middleware('throttle:10,1')
        ->name('access-codes.generate');
    Route::put('/access-codes/{accessCode}', [AdminShoppingAccessCodeController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('access-codes.update');
    Route::delete('/access-codes/{accessCode}', [AdminShoppingAccessCodeController::class, 'destroy'])
        ->name('access-codes.destroy');

    // Medicines management
    Route::resource('medicines', AdminMedicineController::class);
    Route::post('medicines/{medicine}/update-stock', [AdminMedicineController::class, 'updateStock'])->name('medicines.update-stock');
    Route::get('medicines-import', [AdminMedicineImportController::class, 'showImportForm'])->name('medicines.import');
    Route::post('medicines-import', [AdminMedicineImportController::class, 'import'])->name('medicines.import.process');
    Route::get('medicines-import/template', [AdminMedicineImportController::class, 'downloadTemplate'])->name('medicines.import.template');

    // Prescriptions management
    Route::resource('prescriptions', AdminPrescriptionController::class);
    Route::post('prescriptions/{prescription}/update-stock', [AdminPrescriptionController::class, 'updateStock'])->name('prescriptions.update-stock');
    Route::get('prescriptions-import', [AdminPrescriptionImportController::class, 'showImportForm'])->name('prescriptions.import');
    Route::post('prescriptions-import', [AdminPrescriptionImportController::class, 'import'])->name('prescriptions.import.process');

    // Prescription Products management
    Route::resource('prescription-products', AdminPrescriptionProductController::class);
    Route::post('prescription-products/{prescriptionProduct}/update-stock', [AdminPrescriptionProductController::class, 'updateStock'])->name('prescription-products.update-stock');
    Route::get('prescription-products-import', [AdminPrescriptionProductImportController::class, 'showImportForm'])->name('prescription-products.import');
    Route::post('prescription-products-import', [AdminPrescriptionProductImportController::class, 'import'])->name('prescription-products.import.process');
    Route::get('prescription-products-import/template', [AdminPrescriptionProductImportController::class, 'downloadTemplate'])->name('prescription-products.import.template');

    // Produk management
    Route::delete('produk/bulk-delete', [AdminProdukController::class, 'destroyMany'])->name('produk.destroyMany');
    Route::get('produk-pbf', [AdminProdukController::class, 'indexPbf'])->name('produk-pbf.index');
    Route::post('produk-pbf/suppliers', [AdminProdukController::class, 'storePbfSupplier'])->name('produk-pbf.suppliers.store');
    Route::put('produk-pbf/suppliers/{supplier}', [AdminProdukController::class, 'updatePbfSupplier'])->name('produk-pbf.suppliers.update');
    Route::delete('produk-pbf/suppliers/{supplier}', [AdminProdukController::class, 'destroyPbfSupplier'])->name('produk-pbf.suppliers.destroy');
    Route::get('produk-export', [AdminProdukController::class, 'export'])->name('produk.export');
    Route::get('business-data-export', [AdminBusinessDataExportController::class, 'export'])->name('business-data.export');
    Route::resource('produk', AdminProdukController::class);
    Route::post('produk/{produk}/update-stock', [AdminProdukController::class, 'updateStock'])->name('produk.update-stock');
    Route::post('produk/{produk}/update-inline', [AdminProdukController::class, 'updateInline'])->name('produk.update-inline');
    Route::get('produk-import', [AdminProdukImportController::class, 'showImportForm'])->name('produk.import');
    Route::post('produk-import', [AdminProdukImportController::class, 'import'])->name('produk.import.process');
    Route::get('produk-import/template', [AdminProdukImportController::class, 'downloadTemplate'])->name('produk.import.template');

    // Banner / Promo Slideshow management
    Route::resource('banners', AdminBannerController::class);
    Route::post('banners/{banner}/toggle', [AdminBannerController::class, 'toggleAktif'])->name('banners.toggle');

    // Channel-specific medicine discounts
    Route::get('promotions', [AdminMedicinePromotionController::class, 'index'])->name('promotions.index');
    Route::put('promotions/{medicine}', [AdminMedicinePromotionController::class, 'update'])->name('promotions.update');
    Route::get('discount-codes', [AdminDiscountCodeController::class, 'index'])->name('discount-codes.index');
    Route::post('discount-codes', [AdminDiscountCodeController::class, 'store'])->name('discount-codes.store');
    Route::put('discount-codes/{discountCode}', [AdminDiscountCodeController::class, 'update'])->name('discount-codes.update');
    Route::post('discount-codes/{discountCode}/toggle', [AdminDiscountCodeController::class, 'toggle'])->name('discount-codes.toggle');
    Route::delete('discount-codes/{discountCode}', [AdminDiscountCodeController::class, 'destroy'])->name('discount-codes.destroy');
});
