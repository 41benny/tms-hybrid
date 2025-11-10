<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

use App\Http\Controllers\Operations\JobOrderController;

Route::resource('job-orders', JobOrderController::class);
use App\Http\Controllers\Operations\TransportController;

Route::resource('transports', TransportController::class);
Route::post('transports/{transport}/status', [TransportController::class, 'updateStatus'])->name('transports.update-status');
use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\VendorBillController;

Route::resource('invoices', InvoiceController::class);
Route::post('invoices/{invoice}/mark-sent', [InvoiceController::class, 'markAsSent'])->name('invoices.mark-sent');
Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
Route::resource('vendor-bills', VendorBillController::class);
Route::post('vendor-bills/{vendor_bill}/mark-received', [VendorBillController::class, 'markAsReceived'])->name('vendor-bills.mark-received');
Route::post('vendor-bills/{vendor_bill}/mark-paid', [VendorBillController::class, 'markAsPaid'])->name('vendor-bills.mark-paid');
use App\Http\Controllers\Finance\CashBankController;

Route::resource('cash-banks', CashBankController::class)->only(['index', 'create', 'store', 'show']);
use App\Http\Controllers\AiAssistantController;

Route::get('/ai-assistant', [AiAssistantController::class, 'index'])->name('ai-assistant.index');
Route::post('/ai-assistant/ask', [AiAssistantController::class, 'ask'])->name('ai-assistant.ask');
use App\Http\Controllers\Master\CustomerController as MasterCustomerController;
use App\Http\Controllers\Master\DriverController as MasterDriverController;
use App\Http\Controllers\Master\TruckController as MasterTruckController;
use App\Http\Controllers\Master\VendorController as MasterVendorController;

Route::resource('customers', MasterCustomerController::class);
Route::resource('vendors', MasterVendorController::class);
Route::resource('trucks', MasterTruckController::class);
Route::resource('drivers', MasterDriverController::class);
use App\Http\Controllers\Accounting\ReportAccountingController;

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('trial-balance', [ReportAccountingController::class, 'trialBalance'])->name('trial-balance');
    Route::get('general-ledger', [ReportAccountingController::class, 'generalLedger'])->name('general-ledger');
    Route::get('profit-loss', [ReportAccountingController::class, 'profitLoss'])->name('profit-loss');
    Route::get('balance-sheet', [ReportAccountingController::class, 'balanceSheet'])->name('balance-sheet');
});
