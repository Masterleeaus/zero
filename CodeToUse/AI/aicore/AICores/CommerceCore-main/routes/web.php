<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\AdminStore;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ==========================================
// ADMIN PANEL ROUTES
// ==========================================
Route::middleware(['auth', AdminStore::class])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', fn() => redirect()->route('admin.dashboard'));

    // Stores
    Route::resource('stores', StoreController::class);
    Route::get('stores/{store}/settings', [StoreController::class, 'settings'])->name('stores.settings');
    Route::put('stores/{store}/settings', [StoreController::class, 'updateSettings'])->name('stores.settings.update');

    // Categories
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Products
    Route::resource('products', ProductController::class)->except(['show']);
    Route::post('ai/generate-description', [\App\Http\Controllers\Admin\AIController::class, 'generateDescription'])->name('ai.generate');

    // Orders
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('orders/{order}/invoice/{type?}', [\App\Http\Controllers\Admin\InvoiceController::class, 'show'])->name('orders.invoice');

    // Website Builder
    Route::get('builder', [PageBuilderController::class, 'index'])->name('builder.index');
    Route::get('builder/create', [PageBuilderController::class, 'createPage'])->name('builder.create');
    Route::post('builder', [PageBuilderController::class, 'storePage'])->name('builder.store');
    Route::get('builder/{page}/edit', [PageBuilderController::class, 'edit'])->name('builder.edit');
    Route::delete('builder/{page}', [PageBuilderController::class, 'deletePage'])->name('builder.delete');
    Route::post('builder/{page}/sections', [PageBuilderController::class, 'addSection'])->name('builder.sections.add');
    Route::put('builder/sections/{section}', [PageBuilderController::class, 'updateSection'])->name('builder.sections.update');
    Route::post('builder/sections/{section}/toggle', [PageBuilderController::class, 'toggleSection'])->name('builder.sections.toggle');
    Route::delete('builder/sections/{section}', [PageBuilderController::class, 'deleteSection'])->name('builder.sections.delete');
    Route::post('builder/{page}/reorder', [PageBuilderController::class, 'reorder'])->name('builder.reorder');
    Route::get('builder/{page}/preview', [PageBuilderController::class, 'preview'])->name('builder.preview');

    // Agent CRM Routes
    Route::prefix('agent')->name('agent.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AgentDashboardController::class, 'index'])->name('dashboard');
        Route::post('/assign-order', [\App\Http\Controllers\Admin\AgentDashboardController::class, 'assignOrder'])->name('assign');
    });

    // POS routes
    Route::get('/pos', [\App\Http\Controllers\Admin\PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [\App\Http\Controllers\Admin\PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/history', [\App\Http\Controllers\Admin\PosController::class, 'posHistory'])->name('pos.history');
    Route::get('/pos/held', [\App\Http\Controllers\Admin\PosController::class, 'heldOrders'])->name('pos.held');
    Route::post('/pos/hold', [\App\Http\Controllers\Admin\PosController::class, 'holdOrder'])->name('pos.hold');
    Route::post('/pos/recall/{heldOrder}', [\App\Http\Controllers\Admin\PosController::class, 'recallOrder'])->name('pos.recall');
    Route::delete('/pos/held/{heldOrder}', [\App\Http\Controllers\Admin\PosController::class, 'deleteHeldOrder'])->name('pos.held.delete');
    Route::get('/pos/customers/search', [\App\Http\Controllers\Admin\PosController::class, 'searchCustomers'])->name('pos.customers.search');
    Route::post('/pos/customers/register', [\App\Http\Controllers\Admin\PosController::class, 'registerCustomer'])->name('pos.customers.register');

    // ERP routes
    Route::get('/erp', [\App\Http\Controllers\Admin\ErpDashboardController::class, 'index'])->name('erp.dashboard');

    // Shipments & Couriers
    Route::resource('couriers', \App\Http\Controllers\Admin\CourierController::class);
    Route::get('shipments', [\App\Http\Controllers\Admin\ShipmentController::class, 'index'])->name('shipments.index');
    Route::post('orders/{order}/dispatch', [\App\Http\Controllers\Admin\ShipmentController::class, 'dispatchOrder'])->name('orders.dispatch');
    Route::post('shipments/{shipment}/status', [\App\Http\Controllers\Admin\ShipmentController::class, 'updateStatus'])->name('shipments.status');

    // Accounting & Assets
    Route::resource('accounts', \App\Http\Controllers\Admin\AccountController::class);
    Route::resource('journal', \App\Http\Controllers\Admin\JournalEntryController::class)->only(['index', 'store']);
    Route::resource('transactions', \App\Http\Controllers\Admin\TransactionController::class);
    Route::resource('assets', \App\Http\Controllers\Admin\AssetController::class);
    Route::get('reports/accounting', [\App\Http\Controllers\Admin\AccountingReportController::class, 'index'])->name('reports.accounting');

    // HRM System
    Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
    Route::get('attendance', [\App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/clock-in', [\App\Http\Controllers\Admin\AttendanceController::class, 'clockIn'])->name('attendance.clock_in');
    Route::post('attendance/clock-out', [\App\Http\Controllers\Admin\AttendanceController::class, 'clockOut'])->name('attendance.clock_out');
    Route::resource('payroll', \App\Http\Controllers\Admin\PayrollController::class);
    Route::resource('leaves', \App\Http\Controllers\Admin\LeaveController::class);

    // Intelligence & Marketing
    Route::resource('marketing', \App\Http\Controllers\Admin\MarketingController::class);
    Route::post('marketing/settings', [\App\Http\Controllers\Admin\MarketingController::class, 'updateSettings'])->name('marketing.settings');
    Route::get('intelligence', [\App\Http\Controllers\Admin\IntelligenceController::class, 'index'])->name('intelligence.index');
    Route::post('intelligence/generate-campaign', [\App\Http\Controllers\Admin\IntelligenceController::class, 'generateCampaign'])->name('intelligence.generate-campaign');
    Route::get('intelligence/customers', [\App\Http\Controllers\Admin\IntelligenceController::class, 'customers'])->name('intelligence.customers');

    // Branches & Marketing
    Route::resource('branches', \App\Http\Controllers\Admin\BranchController::class);
    Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class);

    // Operational ERP
    Route::resource('expenses', \App\Http\Controllers\Admin\ExpenseController::class);
    Route::resource('inventory-transfers', \App\Http\Controllers\Admin\InventoryTransferController::class);

    Route::get('inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/zone', [\App\Http\Controllers\Admin\InventoryController::class, 'createZone'])->name('inventory.zone.store');
    Route::post('inventory/reorder/auto', [\App\Http\Controllers\Admin\InventoryController::class, 'autoReorder'])->name('inventory.reorder.auto');

    // Purchase Orders
    Route::resource('purchases', \App\Http\Controllers\Admin\PurchaseController::class)->except(['edit', 'update', 'destroy']);
    Route::post('purchases/{purchase}/receive', [\App\Http\Controllers\Admin\PurchaseController::class, 'receive'])->name('purchases.receive');
    Route::post('purchases/{purchase}/cancel', [\App\Http\Controllers\Admin\PurchaseController::class, 'cancel'])->name('purchases.cancel');

    // Suppliers
    Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class);

    // Returns & Refunds
    Route::resource('returns', \App\Http\Controllers\Admin\ReturnController::class)->except(['edit', 'update', 'destroy']);
    Route::post('returns/{saleReturn}/approve', [\App\Http\Controllers\Admin\ReturnController::class, 'approve'])->name('returns.approve');
    Route::post('returns/{saleReturn}/reject', [\App\Http\Controllers\Admin\ReturnController::class, 'reject'])->name('returns.reject');

    // Customer Management
    Route::get('customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{user}', [\App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('customers.show');

    // CRM
    Route::get('/subscribers', [\App\Http\Controllers\Admin\CRMController::class, 'subscribers'])->name('crm.subscribers');
    Route::get('/inquiries', [\App\Http\Controllers\Admin\CRMController::class, 'inquiries'])->name('crm.inquiries');
    Route::patch('/inquiries/{inquiry}/status', [\App\Http\Controllers\Admin\CRMController::class, 'updateInquiryStatus'])->name('crm.inquiries.status');

    // SaaS Billing
    Route::get('billing', [\App\Http\Controllers\Admin\BillingController::class, 'index'])->name('billing.index');
    Route::get('billing/checkout/{plan}', [\App\Http\Controllers\Admin\BillingController::class, 'checkout'])->name('billing.checkout');

    // AI Chat Assistant
    Route::get('ai/chat', [\App\Http\Controllers\Admin\AIChatController::class, 'index'])->name('ai.chat');
    Route::post('ai/chat/ask', [\App\Http\Controllers\Admin\AIChatController::class, 'ask'])->name('ai.chat.ask');
    Route::post('ai/insights/{insight}/dismiss', [\App\Http\Controllers\Admin\AIChatController::class, 'dismissInsight'])->name('ai.insights.dismiss');
    Route::post('ai/alerts/{alert}/resolve', [\App\Http\Controllers\Admin\AIChatController::class, 'resolveAlert'])->name('ai.alerts.resolve');
});

// ==========================================
// PAYMENT GATEWAY CALLBACKS (No Auth Required)
// ==========================================
Route::post('/payment/success', [\App\Http\Controllers\Admin\BillingController::class, 'success'])->name('payment.success');
Route::post('/payment/fail', [\App\Http\Controllers\Admin\BillingController::class, 'fail'])->name('payment.fail');
Route::post('/payment/cancel', [\App\Http\Controllers\Admin\BillingController::class, 'cancel'])->name('payment.cancel');
Route::post('/payment/ipn', [\App\Http\Controllers\Admin\BillingController::class, 'ipn'])->name('payment.ipn');

// ==========================================
// STOREFRONT ROUTES
// ==========================================
Route::prefix('store/{store}')->name('storefront.')->middleware('tenant')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/products', [HomeController::class, 'products'])->name('products');
    Route::get('/product/{product}', [HomeController::class, 'productDetail'])->name('product.detail');
    Route::post('/product/{product}/review', [\App\Http\Controllers\Storefront\ReviewController::class, 'store'])->name('product.review');

    // Account & Loyalty
    Route::middleware('auth')->group(function () {
        Route::get('/account', [\App\Http\Controllers\Storefront\AccountController::class, 'index'])->name('account');
        Route::post('/account/subscription/{order}/cancel', [\App\Http\Controllers\Storefront\AccountController::class, 'cancelSubscription'])->name('account.sub.cancel');
    });

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');

    // Checkout
    Route::get('/checkout', [\App\Http\Controllers\Storefront\CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [\App\Http\Controllers\Storefront\CheckoutController::class, 'store'])->name('checkout.place');
    Route::get('/order/{order_number}/success', [\App\Http\Controllers\Storefront\CheckoutController::class, 'success'])->name('order.success');
    Route::get('/order/{order_number}/confirm', [\App\Http\Controllers\Storefront\CheckoutController::class, 'confirm'])->name('order.confirm');

    // Contact Form
    Route::post('/contact/submit', [\App\Http\Controllers\Storefront\ContactController::class, 'submit'])->name('contact.submit');

    // Newsletter
    Route::post('/newsletter/subscribe', [\App\Http\Controllers\Storefront\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

    // Voice Search
    Route::get('/voice-search', [\App\Http\Controllers\Storefront\VoiceSearchController::class, 'search'])->name('voice.search');
});

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
