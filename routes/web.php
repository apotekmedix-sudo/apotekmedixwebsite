<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminShoppingAccessCodeController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\ShoppingAccessController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminSupplierProcurementController;
use App\Http\Controllers\AdminMedicineController;
use App\Http\Controllers\AdminMedicineImportController;
use App\Http\Controllers\AdminPrescriptionController;
use App\Http\Controllers\AdminPrescriptionImportController;
use App\Http\Controllers\AdminPrescriptionProductController;
use App\Http\Controllers\AdminPrescriptionProductImportController;
use App\Http\Controllers\AdminProdukController;
use App\Http\Controllers\AdminInventoryController;
use App\Http\Controllers\AdminProdukImportController;
use App\Http\Controllers\AdminBusinessDataExportController;
use App\Http\Controllers\AdminMedicinePromotionController;
use App\Http\Controllers\AdminDiscountCodeController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\GassRoomController;
use App\Http\Controllers\KunjunganFileController;
use App\Http\Controllers\DoctorConsultationController;
use App\Http\Controllers\AdminDoctorConsultationController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang-kami', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// Serve uploaded images langsung dari storage/ (banners, promos & medicines)
// Dipakai saat symlink tidak tersedia di hosting
Route::get('/storage/{folder}/{filename}', function (string $folder, string $filename) {
    $allowed = ['banners', 'medicines', 'promos', 'doctors', 'consultation-payments', 'purchase-payment-settings'];
    if (!in_array($folder, $allowed) || basename($filename) !== $filename) abort(404);

    $path = $folder === 'purchase-payment-settings'
        ? Storage::disk('public')->path($folder . '/' . $filename)
        : storage_path($folder . '/' . $filename);
    if (!file_exists($path)) abort(404);

    $mime = mime_content_type($path) ?: 'application/octet-stream';
    return response()->file($path, ['Content-Type' => $mime]);
})->where(['folder' => 'banners|medicines|promos|doctors|consultation-payments|purchase-payment-settings', 'filename' => '.+'])->name('storage.image');

// Products routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/stock-availability', [ProductController::class, 'stockAvailability'])->name('products.stock-availability');
Route::post('/shopping/access/lock/{channel}', [ShoppingAccessController::class, 'lock'])
    ->where('channel', 'b2b')
    ->name('shopping.access.lock');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/product-b2b', [ProductController::class, 'pbf'])->name('products.pbf');
Route::get('/product-b2b/verify', fn () => redirect()->route('products.pbf'));
Route::post('/product-b2b/verify', [ProductController::class, 'pbfVerify'])->middleware('throttle:60,1')->name('products.pbf.verify');
Route::post('/product-b2b/logout', [ProductController::class, 'pbfLogout'])->name('products.pbf.logout');
Route::get('/products-pbf', fn () => redirect()->route('products.pbf'));
Route::get('/products-pbf/verify', fn () => redirect()->route('products.pbf'));
Route::get('/products-apotek', [ProductController::class, 'apotek'])->name('products.apotek');
Route::get('/products-apotek/activate', fn () => redirect()->route('products.apotek'));
Route::post('/products-apotek/activate', [ProductController::class, 'apotekActivate'])
    ->middleware('throttle:60,1')
    ->name('products.apotek.activate');
Route::post('/products-apotek/internal/activate', [ProductController::class, 'internalActivate'])
    ->middleware('throttle:60,1')
    ->name('products.apotek.internal.activate');
Route::post('/products-apotek/access/logout/{channel}', [ProductController::class, 'apotekAccessLogout'])
    ->where('channel', 'b2c|internal')
    ->name('products.apotek.access.logout');
Route::get('/keranjang', [ProductController::class, 'cart'])->name('cart.index');
Route::get('/cart', [ProductController::class, 'cart'])->name('cart.page');
Route::get('/checkout', [ProductController::class, 'checkout'])->name('checkout.index');
Route::post('/checkout/confirm-prescription', [ProductController::class, 'confirmB2cPrescription'])->name('checkout.confirm-prescription');
Route::post('/checkout/validate-discount', [PurchaseHistoryController::class, 'validateDiscountCode'])->name('checkout.validate-discount');
Route::post('/orders/history', [PurchaseHistoryController::class, 'store'])->name('orders.history.store');
Route::post('/invoice/{order}/permit', [PurchaseHistoryController::class, 'updatePermit'])->name('orders.invoice.permit');
Route::get('/invoice/{order}', [PurchaseHistoryController::class, 'invoice'])
    ->middleware('signed')
    ->name('orders.invoice');

Route::prefix('chat-dokter')->name('consultations.')->group(function () {
    Route::get('/', [DoctorConsultationController::class, 'intake'])->name('intake');
    Route::post('/pasien', [DoctorConsultationController::class, 'storePatient'])->middleware('throttle:10,1')->name('patient.store');
    Route::get('/dokter', [DoctorConsultationController::class, 'doctors'])->name('doctors');
    Route::get('/dokter/{doctor}', [DoctorConsultationController::class, 'doctor'])->name('doctor');
    Route::get('/dokter/{doctor}/keluhan', [DoctorConsultationController::class, 'intakeDetails'])->name('details');
    Route::post('/dokter/{doctor}/pasien', [DoctorConsultationController::class, 'selectPatient'])->name('patient.select');
    Route::get('/dokter/{doctor}/pasien/baru', [DoctorConsultationController::class, 'newPatientProfile'])->name('patient.create');
    Route::post('/dokter/{doctor}/pasien/baru', [DoctorConsultationController::class, 'storePatientProfile'])->middleware('throttle:10,1')->name('patient.profile.store');
    Route::get('/dokter/{doctor}/pembayaran', [DoctorConsultationController::class, 'paymentSummary'])->name('payment');
    Route::post('/dokter/{doctor}/konsultasi', [DoctorConsultationController::class, 'store'])->middleware('throttle:10,1')->name('store');
    Route::get('/konsultasi/{token}', [DoctorConsultationController::class, 'patientShow'])->name('patient.show');
    Route::get('/konsultasi/{token}/updates', [DoctorConsultationController::class, 'patientUpdates'])->middleware('throttle:60,1')->name('patient.updates');
    Route::post('/konsultasi/{token}/messages', [DoctorConsultationController::class, 'patientMessage'])->middleware('throttle:30,1')->name('patient.messages.store');
    Route::get('/masuk/{token}', [DoctorConsultationController::class, 'roomJoin'])->middleware('throttle:30,1')->name('room.join');
    Route::post('/masuk/{token}', [DoctorConsultationController::class, 'redeemRoomLink'])->middleware('throttle:10,1')->name('room.redeem');
    Route::get('/room/{consultation}', [DoctorConsultationController::class, 'room'])->name('room');
    Route::get('/room/{consultation}/updates', [DoctorConsultationController::class, 'roomUpdates'])->middleware('throttle:60,1')->name('room.updates');
    Route::post('/room/{consultation}/messages', [DoctorConsultationController::class, 'roomMessage'])->middleware('throttle:30,1')->name('room.messages.store');
    Route::get('/room/{consultation}/products', [DoctorConsultationController::class, 'doctorProducts'])->middleware('throttle:30,1')->name('room.products');
    Route::post('/room/{consultation}/prescriptions', [DoctorConsultationController::class, 'issuePrescription'])->middleware('throttle:10,1')->name('room.prescriptions.store');
    Route::post('/room/{consultation}/end', [DoctorConsultationController::class, 'endRoomSession'])->name('room.end');
});

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
    Route::put('/payment-settings', [CashierController::class, 'updatePaymentSettings'])->name('payment-settings.update');
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
    ->middleware('throttle:60,1')
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
Route::get('/kunjungan/access', [KunjunganFileController::class, 'accessForm'])->name('kunjungan.access.form');
Route::post('/kunjungan/access', [KunjunganFileController::class, 'access'])
    ->middleware('throttle:5,1')
    ->name('kunjungan.access');
Route::get('/kunjungan/files', [KunjunganFileController::class, 'index'])->name('kunjungan.room');
Route::post('/kunjungan/files', [KunjunganFileController::class, 'upload'])
    ->middleware('throttle:10,1')
    ->name('kunjungan.files.upload');
Route::get('/kunjungan/files/{file}/download', [KunjunganFileController::class, 'download'])
    ->name('kunjungan.files.download');
Route::post('/kunjungan/leave', [KunjunganFileController::class, 'leave'])->name('kunjungan.leave');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications', [AdminDashboardController::class, 'notifications'])->name('notifications.index');
    Route::post('/notifications/{order}/mark-paid', [AdminDashboardController::class, 'markTempoPaid'])->name('notifications.mark-paid');
    Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/consultations', [AdminDoctorConsultationController::class, 'index'])->name('consultations.index');
    Route::get('/consultations/doctors/create', [AdminDoctorConsultationController::class, 'createDoctor'])->name('consultations.doctors.create');
    Route::post('/consultations/doctors', [AdminDoctorConsultationController::class, 'storeDoctor'])->name('consultations.doctors.store');
    Route::get('/consultations/doctors/{doctor}/edit', [AdminDoctorConsultationController::class, 'editDoctor'])->name('consultations.doctors.edit');
    Route::put('/consultations/doctors/{doctor}', [AdminDoctorConsultationController::class, 'updateDoctor'])->name('consultations.doctors.update');
    Route::delete('/consultations/doctors/{doctor}', [AdminDoctorConsultationController::class, 'deactivateDoctor'])->name('consultations.doctors.deactivate');
    Route::put('/consultations/payment-settings', [AdminDoctorConsultationController::class, 'updatePaymentSettings'])->name('consultations.payment-settings.update');
    Route::get('/consultations/patients/export', [AdminDoctorConsultationController::class, 'exportPatients'])->name('consultations.patients.export');
    Route::get('/consultations/{consultation}/payment-proof', [AdminDoctorConsultationController::class, 'viewPaymentProof'])->name('consultations.payment-proof');
    Route::get('/consultations/{consultation}/chat', [AdminDoctorConsultationController::class, 'chatHistory'])->name('consultations.chat');
    Route::delete('/consultations/{consultation}', [AdminDoctorConsultationController::class, 'deleteCompletedSession'])->name('consultations.delete');
    Route::post('/consultations/{consultation}/mark-paid', [AdminDoctorConsultationController::class, 'markPaid'])->name('consultations.mark-paid');
    Route::post('/consultations/{consultation}/reject-payment', [AdminDoctorConsultationController::class, 'rejectPayment'])->name('consultations.reject-payment');
    Route::post('/consultations/{consultation}/end-session', [AdminDoctorConsultationController::class, 'endSession'])->name('consultations.end-session');
    Route::post('/consultations/{consultation}/room-invitations', [AdminDoctorConsultationController::class, 'resendRoomInvitations'])->name('consultations.room-invitations');
    Route::post('/consultations/{consultation}/cancel', [AdminDoctorConsultationController::class, 'cancel'])->name('consultations.cancel');
    Route::get('/purchase-history', [AdminDashboardController::class, 'purchaseHistory'])->name('purchase-history.index');
    Route::get('/supplier-procurement', [AdminSupplierProcurementController::class, 'index'])->name('supplier-procurement.index');
    Route::get('/supplier-procurement/medicines/search', [AdminSupplierProcurementController::class, 'searchMedicines'])->name('supplier-procurement.medicines.search');
    Route::post('/supplier-procurement/offers', [AdminSupplierProcurementController::class, 'storeOffer'])->name('supplier-procurement.offers.store');
    Route::get('/supplier-procurement/offers/template', [AdminSupplierProcurementController::class, 'downloadOfferTemplate'])->name('supplier-procurement.offers.template');
    Route::post('/supplier-procurement/offers/import', [AdminSupplierProcurementController::class, 'importOffers'])->name('supplier-procurement.offers.import');
    Route::post('/supplier-procurement/orders', [AdminSupplierProcurementController::class, 'storeOrder'])->name('supplier-procurement.orders.store');
    Route::get('/supplier-procurement/orders/{order}/purchase-order', [AdminSupplierProcurementController::class, 'purchaseOrder'])->name('supplier-procurement.orders.purchase-order');
    Route::post('/supplier-procurement/orders/{order}/receive', [AdminSupplierProcurementController::class, 'receive'])->name('supplier-procurement.orders.receive');
    Route::post('/supplier-procurement/orders/{order}/return', [AdminSupplierProcurementController::class, 'returnOrder'])->name('supplier-procurement.orders.return');
    Route::delete('/supplier-procurement/orders/{order}', [AdminSupplierProcurementController::class, 'destroyOrder'])->name('supplier-procurement.orders.destroy');
    Route::post('/purchase-history/{order}/receive', [AdminSupplierProcurementController::class, 'receiveLegacyOrder'])->name('purchase-history.receive');
    Route::post('/purchase-history/{order}/return', [AdminSupplierProcurementController::class, 'returnLegacyOrder'])->name('purchase-history.return');
    Route::delete('/purchase-history/{order}/returned', [AdminSupplierProcurementController::class, 'destroyLegacyOrder'])->name('purchase-history.destroy-returned');
    Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
    Route::get('/purchase-history/{order}/purchase-order', [PurchaseHistoryController::class, 'purchaseOrder'])->name('purchase-history.purchase-order');
    Route::get('/purchase-history/export', [AdminDashboardController::class, 'exportPurchaseHistory'])->name('purchase-history.export');
    Route::post('/purchase-history/{order}/approval', [AdminDashboardController::class, 'updateApprovalStatus'])->name('purchase-history.approval');
    Route::delete('/purchase-history/{order}', [AdminDashboardController::class, 'destroy'])->name('purchase-history.destroy');
    Route::delete('/purchase-history', [AdminDashboardController::class, 'destroyAll'])->name('purchase-history.destroyAll');
    Route::get('/customers/export', [AdminCustomerController::class, 'export'])->name('customers.export');
    Route::get('/customers/export/master', [AdminCustomerController::class, 'exportMaster'])->name('customers.export.master');
    Route::get('/customers/import/template', [AdminCustomerController::class, 'downloadImportTemplate'])->name('customers.import.template');
    Route::post('/customers/import', [AdminCustomerController::class, 'import'])->name('customers.import');
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
    Route::get('produk-pbf/suppliers/export', [AdminProdukController::class, 'exportPbfSuppliers'])->name('produk-pbf.suppliers.export');
    Route::get('produk-pbf/suppliers/import/template', [AdminProdukController::class, 'downloadPbfSupplierImportTemplate'])->name('produk-pbf.suppliers.import.template');
    Route::post('produk-pbf/suppliers/import', [AdminProdukController::class, 'importPbfSuppliers'])->name('produk-pbf.suppliers.import');
    Route::put('produk-pbf/suppliers/{supplier}', [AdminProdukController::class, 'updatePbfSupplier'])->name('produk-pbf.suppliers.update');
    Route::delete('produk-pbf/suppliers/{supplier}', [AdminProdukController::class, 'destroyPbfSupplier'])->name('produk-pbf.suppliers.destroy');
    Route::get('produk-export', [AdminProdukController::class, 'export'])->name('produk.export');
    Route::get('business-data-export', [AdminBusinessDataExportController::class, 'export'])->name('business-data.export');
    Route::resource('produk', AdminProdukController::class);
    Route::post('produk/{produk}/update-stock', [AdminProdukController::class, 'updateStock'])->name('produk.update-stock');
    Route::post('produk/{produk}/update-inline', [AdminProdukController::class, 'updateInline'])->name('produk.update-inline');
    Route::get('produk-import', [AdminProdukImportController::class, 'showImportForm'])->name('produk.import');
    Route::post('produk-import', [AdminProdukImportController::class, 'import'])->name('produk.import.process');
    Route::post('produk-import/batch', [AdminProdukImportController::class, 'importBatch'])->name('produk.import.batch');
    Route::get('produk-import/template', [AdminProdukImportController::class, 'downloadTemplate'])->name('produk.import.template');

    // Channel-specific medicine discounts
    Route::get('promotions', [AdminMedicinePromotionController::class, 'index'])->name('promotions.index');
    Route::put('promotions/{medicine}', [AdminMedicinePromotionController::class, 'update'])->name('promotions.update');
    Route::get('discount-codes', [AdminDiscountCodeController::class, 'index'])->name('discount-codes.index');
    Route::post('discount-codes', [AdminDiscountCodeController::class, 'store'])->name('discount-codes.store');
    Route::put('discount-codes/{discountCode}', [AdminDiscountCodeController::class, 'update'])->name('discount-codes.update');
    Route::post('discount-codes/{discountCode}/toggle', [AdminDiscountCodeController::class, 'toggle'])->name('discount-codes.toggle');
    Route::delete('discount-codes/{discountCode}', [AdminDiscountCodeController::class, 'destroy'])->name('discount-codes.destroy');
});
