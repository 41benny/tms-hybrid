<?php

use App\Http\Controllers\Accounting\ReportAccountingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Finance\CashBankController;
use App\Http\Controllers\Finance\DriverAdvanceController;
use App\Http\Controllers\Finance\HutangController;
use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\PaymentRequestController;
use App\Http\Controllers\Finance\VendorBillController;
use App\Http\Controllers\Master\CustomerController as MasterCustomerController;
use App\Http\Controllers\Master\DriverController as MasterDriverController;
use App\Http\Controllers\Master\EquipmentController as MasterEquipmentController;
use App\Http\Controllers\Master\SalesController as MasterSalesController;
use App\Http\Controllers\Master\TruckController as MasterTruckController;
use App\Http\Controllers\Master\VendorController as MasterVendorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Operations\JobOrderController;
use App\Http\Controllers\Operations\ShipmentLegController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'active'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::resource('job-orders', JobOrderController::class);
    Route::post('job-orders/{job_order}/cancel', [JobOrderController::class, 'cancel'])->name('job-orders.cancel');
    Route::get('job-orders/{jobOrder}/legs/create', [ShipmentLegController::class, 'create'])->name('job-orders.legs.create');
    Route::post('job-orders/{jobOrder}/legs', [ShipmentLegController::class, 'store'])->name('job-orders.legs.store');
    Route::get('job-orders/{jobOrder}/legs/{leg}/edit', [ShipmentLegController::class, 'edit'])->name('job-orders.legs.edit');
    Route::put('job-orders/{jobOrder}/legs/{leg}', [ShipmentLegController::class, 'update'])->name('job-orders.legs.update');
    Route::delete('job-orders/{jobOrder}/legs/{leg}', [ShipmentLegController::class, 'destroy'])->name('job-orders.legs.destroy');
    Route::post('legs/{leg}/additional-costs', [ShipmentLegController::class, 'storeAdditionalCost'])->name('legs.additional-costs.store');
    Route::put('additional-costs/{cost}', [ShipmentLegController::class, 'updateAdditionalCost'])->name('additional-costs.update');
    Route::delete('additional-costs/{cost}', [ShipmentLegController::class, 'destroyAdditionalCost'])->name('additional-costs.destroy');
    Route::post('legs/{leg}/generate-vendor-bill', [ShipmentLegController::class, 'generateVendorBill'])->name('legs.generate-vendor-bill');
    Route::get('api/truck-driver', [ShipmentLegController::class, 'getDriverByTruck'])->name('api.truck-driver');

    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/mark-sent', [InvoiceController::class, 'markAsSent'])->name('invoices.mark-sent');
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');

    Route::resource('vendor-bills', VendorBillController::class)->only(['index', 'show']);
    Route::post('vendor-bills/{vendor_bill}/mark-received', [VendorBillController::class, 'markAsReceived'])->name('vendor-bills.mark-received');
    Route::post('vendor-bills/{vendor_bill}/mark-paid', [VendorBillController::class, 'markAsPaid'])->name('vendor-bills.mark-paid');

    Route::resource('payment-requests', PaymentRequestController::class)->except(['edit', 'update']);
    Route::post('payment-requests/{payment_request}/approve', [PaymentRequestController::class, 'approve'])->name('payment-requests.approve');
    Route::post('payment-requests/{payment_request}/reject', [PaymentRequestController::class, 'reject'])->name('payment-requests.reject');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('count', [NotificationController::class, 'count'])->name('count');
        Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    });

    Route::get('hutang', [HutangController::class, 'dashboard'])->name('hutang.dashboard');
    Route::resource('driver-advances', DriverAdvanceController::class)->only(['index', 'show']);
    Route::post('driver-advances/{driverAdvance}/pay-dp', [DriverAdvanceController::class, 'payDP'])->name('driver-advances.pay-dp');
    Route::post('driver-advances/{driverAdvance}/settlement', [DriverAdvanceController::class, 'processSettlement'])->name('driver-advances.settlement');

    Route::resource('cash-banks', CashBankController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('/ai-assistant', [AiAssistantController::class, 'index'])->name('ai-assistant.index');
    Route::post('/ai-assistant/ask', [AiAssistantController::class, 'ask'])->name('ai-assistant.ask');

    Route::resource('customers', MasterCustomerController::class);
    Route::resource('vendors', MasterVendorController::class);
    Route::resource('trucks', MasterTruckController::class);
    Route::resource('drivers', MasterDriverController::class);
    Route::resource('sales', MasterSalesController::class);
    Route::resource('equipment', MasterEquipmentController::class);

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('trial-balance', [ReportAccountingController::class, 'trialBalance'])->name('trial-balance');
        Route::get('general-ledger', [ReportAccountingController::class, 'generalLedger'])->name('general-ledger');
        Route::get('profit-loss', [ReportAccountingController::class, 'profitLoss'])->name('profit-loss');
        Route::get('balance-sheet', [ReportAccountingController::class, 'balanceSheet'])->name('balance-sheet');
    });
});

Route::middleware(['auth', 'active', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class)->except(['show']);
});
