<?php

use App\Http\Controllers\Accounting\ChartOfAccountController;
use App\Http\Controllers\Accounting\JournalController;
use App\Http\Controllers\Accounting\ReportAccountingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Finance\CashBankController;
use App\Http\Controllers\Finance\DriverAdvanceController;
use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Finance\HutangController;
use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\PaymentRequestController;
use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\Finance\VendorBillController;
use App\Http\Controllers\Inventory\PartController;
use App\Http\Controllers\Inventory\PartDashboardController;
use App\Http\Controllers\Inventory\PartPurchaseController;
use App\Http\Controllers\Inventory\PartUsageController;
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

    // Sales-friendly console (mobile first)
    Route::get('sales-console', [\App\Http\Controllers\SalesDashboardController::class, 'index'])
        ->name('sales.console');

    Route::get('finance/dashboard', [FinanceDashboardController::class, 'index'])->name('finance.dashboard');

    Route::middleware('menu:invoices')->group(function () {
        Route::get('invoices/approvals', [InvoiceController::class, 'approvals'])
            ->name('invoices.approvals')
            ->middleware('permission:invoices.approve');

        Route::resource('invoices', InvoiceController::class);

        Route::patch('invoices/{invoice}/mark-as-sent', [InvoiceController::class, 'markAsSent'])
            ->name('invoices.mark-as-sent')
            ->middleware('permission:invoices.manage_status');

        Route::patch('invoices/{invoice}/revert-to-draft', [InvoiceController::class, 'revertToDraft'])
            ->name('invoices.revert-to-draft')
            ->middleware('permission:invoices.manage_status');

        Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])
            ->name('invoices.cancel')
            ->middleware('permission:invoices.cancel');

        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

        Route::patch('invoices/{invoice}/submit-approval', [InvoiceController::class, 'submitForApproval'])
            ->name('invoices.submit-approval')
            ->middleware('permission:invoices.submit');

        Route::patch('invoices/{invoice}/approve', [InvoiceController::class, 'approve'])
            ->name('invoices.approve')
            ->middleware('permission:invoices.approve');

        Route::patch('invoices/{invoice}/reject', [InvoiceController::class, 'reject'])
            ->name('invoices.reject')
            ->middleware('permission:invoices.approve');

        Route::get('invoices/{invoice}/revise', [InvoiceController::class, 'revise'])
            ->name('invoices.revise')
            ->middleware('permission:invoices.update');

        Route::post('invoices/{invoice}/revise', [InvoiceController::class, 'storeRevision'])
            ->name('invoices.store-revision')
            ->middleware('permission:invoices.update');
    });

    Route::resource('vendor-bills', VendorBillController::class)->only(['index', 'show']);
    Route::post('vendor-bills/{vendor_bill}/mark-received', [VendorBillController::class, 'markAsReceived'])->name('vendor-bills.mark-received');
    Route::post('vendor-bills/{vendor_bill}/mark-paid', [VendorBillController::class, 'markAsPaid'])->name('vendor-bills.mark-paid');
    Route::get('vendor-bills/{vendor_bill}/leg-info', [VendorBillController::class, 'getLegInfo'])->name('vendor-bills.leg-info');
    Route::get('vendor-bills/{vendor_bill}/job-info', [VendorBillController::class, 'getJobInfo'])->name('vendor-bills.job-info');

    Route::resource('payment-requests', PaymentRequestController::class)->except(['edit', 'update']);
    Route::post('payment-requests/{payment_request}/approve', [PaymentRequestController::class, 'approve'])->name('payment-requests.approve');
    Route::post('payment-requests/{payment_request}/reject', [PaymentRequestController::class, 'reject'])->name('payment-requests.reject');
    Route::get('payment-requests/{payment_request}/job-info', [PaymentRequestController::class, 'getJobInfo'])->name('payment-requests.job-info');

    Route::resource('payment-receipts', PaymentReceiptController::class)->except(['edit', 'update']);
    Route::post('payment-receipts/{payment_receipt}/allocate', [PaymentReceiptController::class, 'allocate'])->name('payment-receipts.allocate');
    Route::delete('payment-receipts/{payment_receipt}/deallocate', [PaymentReceiptController::class, 'deallocate'])->name('payment-receipts.deallocate');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('count', [NotificationController::class, 'count'])->name('count');
        Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    });

    Route::get('hutang', [HutangController::class, 'dashboard'])->name('hutang.dashboard')->middleware('menu:hutang');
    Route::resource('driver-advances', DriverAdvanceController::class)->only(['index', 'show']);
    Route::post('driver-advances/{driverAdvance}/post', [\App\Http\Controllers\Operations\DriverAdvanceController::class, 'post'])->name('driver-advances.post');
    Route::post('driver-advances/{driverAdvance}/unpost', [\App\Http\Controllers\Operations\DriverAdvanceController::class, 'unpost'])->name('driver-advances.unpost');
    Route::post('driver-advances/{driverAdvance}/pay-dp', [DriverAdvanceController::class, 'payDP'])->name('driver-advances.pay-dp');
    Route::post('driver-advances/{driverAdvance}/settlement', [DriverAdvanceController::class, 'processSettlement'])->name('driver-advances.settlement');

    Route::resource('cash-banks', CashBankController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('cash-banks/{cashBankTransaction}/print', [CashBankController::class, 'print'])->name('cash-banks.print');
    Route::delete('cash-banks/{cashBankTransaction}/cancel', [CashBankController::class, 'cancel'])->name('cash-banks.cancel');

    Route::resource('journals', JournalController::class)->except(['destroy']);
    Route::resource('chart-of-accounts', ChartOfAccountController::class)->except(['show', 'destroy']);

    // Fixed Assets
    Route::resource('fixed-assets', \App\Http\Controllers\Accounting\FixedAssetController::class);
    Route::post('fixed-assets/{fixedAsset}/depreciate', [\App\Http\Controllers\Accounting\FixedAssetController::class, 'depreciate'])->name('fixed-assets.depreciate');
    Route::get('fixed-assets/{fixedAsset}/dispose', [\App\Http\Controllers\Accounting\FixedAssetController::class, 'disposeForm'])->name('fixed-assets.dispose.form');
    Route::post('fixed-assets/{fixedAsset}/dispose', [\App\Http\Controllers\Accounting\FixedAssetController::class, 'dispose'])->name('fixed-assets.dispose');

    Route::get('inventory/dashboard', [PartDashboardController::class, 'index'])->name('inventory.dashboard');
    Route::resource('parts', PartController::class);
    Route::resource('part-purchases', PartPurchaseController::class)->except(['edit', 'update', 'destroy']);
    Route::resource('part-usages', PartUsageController::class)->except(['edit', 'update', 'destroy']);

    Route::get('/ai-assistant', [AiAssistantController::class, 'index'])->name('ai-assistant.index');
    Route::post('/ai-assistant/ask', [AiAssistantController::class, 'ask'])->name('ai-assistant.ask');

    Route::resource('customers', MasterCustomerController::class);
    Route::resource('vendors', MasterVendorController::class);
    Route::resource('trucks', MasterTruckController::class);
    Route::resource('drivers', MasterDriverController::class);
    Route::resource('sales', MasterSalesController::class);
    Route::resource('equipment', MasterEquipmentController::class);

    // Master Cash/Bank Accounts
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('cash-bank-accounts', \App\Http\Controllers\Master\CashBankAccountController::class);
        Route::post('cash-bank-accounts/{cash_bank_account}/activate', [\App\Http\Controllers\Master\CashBankAccountController::class, 'activate'])->name('cash-bank-accounts.activate');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('trial-balance', [ReportAccountingController::class, 'trialBalance'])->name('trial-balance');
        Route::get('general-ledger', [ReportAccountingController::class, 'generalLedger'])->name('general-ledger');
        Route::get('profit-loss', [ReportAccountingController::class, 'profitLoss'])->name('profit-loss');
        Route::get('balance-sheet', [ReportAccountingController::class, 'balanceSheet'])->name('balance-sheet');

        // Tax Reports
        Route::prefix('tax')->name('tax.')->group(function () {
            Route::get('ppn-keluaran', [\App\Http\Controllers\Accounting\TaxReportController::class, 'ppnKeluaran'])->name('ppn-keluaran');
            Route::get('ppn-masukan', [\App\Http\Controllers\Accounting\TaxReportController::class, 'ppnMasukan'])->name('ppn-masukan');
            Route::get('ppn-summary', [\App\Http\Controllers\Accounting\TaxReportController::class, 'ppnSummary'])->name('ppn-summary');
            Route::get('pph23-dipotong', [\App\Http\Controllers\Accounting\TaxReportController::class, 'pph23Dipotong'])->name('pph23-dipotong');
            Route::get('pph23-dipungut', [\App\Http\Controllers\Accounting\TaxReportController::class, 'pph23Dipungut'])->name('pph23-dipungut');
            Route::get('pph23-summary', [\App\Http\Controllers\Accounting\TaxReportController::class, 'pph23Summary'])->name('pph23-summary');
        });
    });

    // Tax Menus
    Route::prefix('tax')->name('tax.')->group(function () {
        Route::get('ppn', [\App\Http\Controllers\Accounting\TaxReportController::class, 'ppnSummary'])->name('ppn.index');
        Route::get('pph23', [\App\Http\Controllers\Accounting\TaxReportController::class, 'pph23Summary'])->name('pph23.index');
    });

    // Tax Invoice Requests
    Route::prefix('tax-invoices')->name('tax-invoices.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Accounting\TaxInvoiceRequestController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Accounting\TaxInvoiceRequestController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Accounting\TaxInvoiceRequestController::class, 'store'])->name('store');
        Route::post('/extract', [\App\Http\Controllers\Accounting\TaxInvoiceRequestController::class, 'extractFile'])->name('extract');
        Route::get('/export', [\App\Http\Controllers\Accounting\TaxInvoiceRequestController::class, 'export'])->name('export');
        Route::get('/{taxInvoiceRequest}', [\App\Http\Controllers\Accounting\TaxInvoiceRequestController::class, 'show'])->name('show');
        Route::get('/{taxInvoiceRequest}/complete', [\App\Http\Controllers\Accounting\TaxInvoiceRequestController::class, 'complete'])->name('complete');
        Route::put('/{taxInvoiceRequest}/complete', [\App\Http\Controllers\Accounting\TaxInvoiceRequestController::class, 'updateComplete'])->name('update-complete');
        Route::get('/{taxInvoiceRequest}/preview', [\App\Http\Controllers\Accounting\TaxInvoiceRequestController::class, 'preview'])->name('preview');
        Route::get('/{taxInvoiceRequest}/download', [\App\Http\Controllers\Accounting\TaxInvoiceRequestController::class, 'downloadFile'])->name('download');
    });

    // Accounting Periods
    Route::prefix('accounting/periods')->name('accounting.periods.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Accounting\FiscalPeriodController::class, 'index'])->name('index');
        Route::post('create', [\App\Http\Controllers\Accounting\FiscalPeriodController::class, 'createPeriod'])->name('create');
        Route::post('create-current', [\App\Http\Controllers\Accounting\FiscalPeriodController::class, 'createCurrentMonth'])->name('create-current');
        Route::post('{period}/close', [\App\Http\Controllers\Accounting\FiscalPeriodController::class, 'close'])->name('close');
        Route::post('{period}/reopen', [\App\Http\Controllers\Accounting\FiscalPeriodController::class, 'reopen'])->name('reopen');
        Route::post('{period}/lock', [\App\Http\Controllers\Accounting\FiscalPeriodController::class, 'lock'])->name('lock');
    });
});

Route::middleware(['auth', 'active', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class)->except(['show']);
});
