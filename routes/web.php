<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

use App\Http\Controllers\Operations\JobOrderController;
use App\Http\Controllers\Operations\ShipmentLegController;

Route::resource('job-orders', JobOrderController::class);
Route::get('job-orders/{jobOrder}/legs/create', [ShipmentLegController::class, 'create'])->name('job-orders.legs.create');
Route::post('job-orders/{jobOrder}/legs', [ShipmentLegController::class, 'store'])->name('job-orders.legs.store');
Route::get('job-orders/{jobOrder}/legs/{leg}/edit', [ShipmentLegController::class, 'edit'])->name('job-orders.legs.edit');
Route::put('job-orders/{jobOrder}/legs/{leg}', [ShipmentLegController::class, 'update'])->name('job-orders.legs.update');
Route::delete('job-orders/{jobOrder}/legs/{leg}', [ShipmentLegController::class, 'destroy'])->name('job-orders.legs.destroy');
Route::post('legs/{leg}/additional-costs', [ShipmentLegController::class, 'storeAdditionalCost'])->name('legs.additional-costs.store');
Route::put('additional-costs/{cost}', [ShipmentLegController::class, 'updateAdditionalCost'])->name('additional-costs.update');
Route::delete('additional-costs/{cost}', [ShipmentLegController::class, 'destroyAdditionalCost'])->name('additional-costs.destroy');
Route::get('api/truck-driver', [ShipmentLegController::class, 'getDriverByTruck'])->name('api.truck-driver');

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
use App\Http\Controllers\Master\EquipmentController as MasterEquipmentController;
use App\Http\Controllers\Master\SalesController as MasterSalesController;
use App\Http\Controllers\Master\TruckController as MasterTruckController;
use App\Http\Controllers\Master\VendorController as MasterVendorController;

Route::resource('customers', MasterCustomerController::class);
Route::resource('vendors', MasterVendorController::class);
Route::resource('trucks', MasterTruckController::class);
Route::resource('drivers', MasterDriverController::class);
Route::resource('sales', MasterSalesController::class);
Route::resource('equipment', MasterEquipmentController::class);
use App\Http\Controllers\Accounting\ReportAccountingController;

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('trial-balance', [ReportAccountingController::class, 'trialBalance'])->name('trial-balance');
    Route::get('general-ledger', [ReportAccountingController::class, 'generalLedger'])->name('general-ledger');
    Route::get('profit-loss', [ReportAccountingController::class, 'profitLoss'])->name('profit-loss');
    Route::get('balance-sheet', [ReportAccountingController::class, 'balanceSheet'])->name('balance-sheet');
});
