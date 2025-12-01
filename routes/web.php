<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashbboardController;
use App\Http\Controllers\DiskController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceDraftController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\YearController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

// Backup Database
Route::get('/export-db', [DiskController::class, 'exportDatabase'])->name('admin.exportDatabase');



Route::get('/dashboard', [DashbboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Switch active year database (cookie based)
    Route::get('/switch-year/{year}', [YearController::class, 'switch'])
        ->name('switch.year');

    // ✅ Create New Year Database
    Route::post('/create-new-year', [YearController::class, 'create'])
        ->name('year.create');

    // Category
    Route::post('/set-category', [CategoryController::class, 'setCategory'])->name('set.category');
    Route::get('/get-category', [CategoryController::class, 'getCategory'])->name('category.get');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customers
    Route::get('/customers/{id}/rental-details', [CustomerController::class, 'rentalDetails'])->name('customers.rentalDetails');
    Route::get('/customer/{id}', [CustomerController::class, 'getCustomer']);
    Route::resource('customers', CustomerController::class);

    // Products
    Route::get('/products/{id}/rental-details', [ProductController::class, 'rentalDetails'])->name('products.rentalDetails');
    Route::resource('products', ProductController::class);

    // Invoices
    Route::get('/invoices/unpaid', [InvoiceController::class, 'unpaid'])->name('invoices.unpaid');
    Route::get('/invoices/paid', [InvoiceController::class, 'paid'])->name('invoices.paid');
    Route::put('/invoices/{invoice}/note', [InvoiceController::class, 'updateNote'])->name('invoices.updateNote');



    Route::resource('invoices', InvoiceController::class);
    Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{id}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('/invoices/customer/store', [InvoiceController::class, 'customer_store'])->name('invoices.customer.store');
    Route::post('/invoices/{id}/add-payment', [InvoiceController::class, 'addPayment'])->name('invoices.addPayment');
    Route::patch('/invoices/{id}/update-invoice-status', [InvoiceController::class, 'updateInvoiceStatus'])->name('invoices.updateInvoiceStatus');

    // Manage returns
    Route::post('/invoices/{invoice}/process-returns', [InvoiceController::class, 'processReturns'])->name('invoices.process-returns');
    // Add new items
    Route::post('/invoices/{invoice}/add-items', [InvoiceController::class, 'addItems'])->name('invoices.add-items');
    //Remove items
    Route::get('/invoices/{invoice}/remove-items', [InvoiceController::class, 'showRemoveItems'])->name('invoices.removeItems');
    Route::delete('/invoice-items/{item}', [InvoiceController::class, 'destroyItem'])->name('invoice-items.destroyItem');
    Route::delete('/custom-items/{id}', [InvoiceController::class, 'destroyCustom'])->name('custom-items.destroy');
    Route::delete('/additional-items/{id}', [InvoiceController::class, 'destroyAdditional'])->name('additional-items.destroy');
    //Add new dates
    Route::post('/invoices/{invoice}/add-dates', [InvoiceController::class, 'addDates'])->name('invoices.add-dates');

    // Drafts
    Route::resource('drafts', DraftController::class);


    // POS
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
    Route::post('/pos/customer/store', [POSController::class, 'store'])->name('pos.store');

    // Trial balance
    Route::get('/trial-balance', [DashbboardController::class, 'trialBalance'])->name('trialbalance.index');
    Route::get('/trial-balance/products', [DashbboardController::class, 'trialBalanceByProducts'])->name('trialbalance.products');

    // Users
    Route::resource('users', UserController::class);
});

require __DIR__ . '/auth.php';
