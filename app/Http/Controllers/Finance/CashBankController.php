<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Finance\CashBankAccount;
use App\Models\Finance\CashBankTransaction;
use App\Models\Finance\Invoice;
use App\Models\Finance\VendorBill;
use Illuminate\Http\Request;

class CashBankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $accounts = CashBankAccount::orderBy('name')->get();
        $query = CashBankTransaction::query()->with(['account', 'invoice', 'vendorBill', 'customer', 'vendor']);
        
        if ($acc = $request->get('cash_bank_account_id')) {
            $query->where('cash_bank_account_id', $acc);
        }
        if ($src = $request->get('sumber')) {
            $query->where('sumber', $src);
        }
        
        // Date filters for Main Query
        if ($from = $request->get('from')) {
            $query->whereDate('tanggal', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('tanggal', '<=', $to);
        }
        
        // Exclude voided transactions by default (unless show_voided=1)
        if (!$request->get('show_voided')) {
            $query->whereNull('voided_at');
        }
        
        $transactions = $query->latest('tanggal')->latest('id')->paginate(20)->withQueryString();

        // --- Summary Calculation (Net Amount) ---
        // Net = Amount - PPh23 - Admin
        $netRaw = "(amount - COALESCE(withholding_pph23, 0) - COALESCE(admin_fee, 0))";
        
        // Clone for Summary (In/Out/Net)
        $summaryQuery = (clone $query);
        // We need to re-apply the base where clauses if we cloned from a modified query? 
        // $query is already modified with wheres.
        // But for Summary we want the sum of the *current filtered view*.
        // Need to be careful with 'latest' and 'paginate' which are not applied to $query object itself yet (paginate returns result, but query builder keeps state? No, paginate is terminal... wait, $query is Builder).
        
        // Summary 'In'
        $summary['in'] = (clone $query)->where('jenis', 'cash_in')->sum(\DB::raw($netRaw));
        // Summary 'Out'
        $summary['out'] = (clone $query)->where('jenis', 'cash_out')->sum(\DB::raw($netRaw));
        $summary['net'] = $summary['in'] - $summary['out'];

        // --- Running Balance Calculation ---
        // 1. Calculate Opening Balance (Before 'from' date, match other filters)
        $openingBalance = 0;
        if ($from) {
            $openingQuery = CashBankTransaction::query();
            if ($acc) $openingQuery->where('cash_bank_account_id', $acc);
            if ($src) $openingQuery->where('sumber', $src);
            if (!$request->get('show_voided')) $openingQuery->whereNull('voided_at');
            
            $openingQuery->whereDate('tanggal', '<', $from);
            
            $signedNetRaw = "(CASE WHEN jenis='cash_in' THEN 1 ELSE -1 END) * " . $netRaw;
            $openingBalance = $openingQuery->sum(\DB::raw($signedNetRaw));
        }

        // 2. Calculate Total Movement in Current View (All Pages)
        $signedNetRaw = "(CASE WHEN jenis='cash_in' THEN 1 ELSE -1 END) * " . $netRaw;
        $totalInView = (clone $query)->sum(\DB::raw($signedNetRaw));

        // 3. Calculate Movement of Skipped Items (Newer items on previous pages)
        // Since we order by Latest, "Skipped" items are the NEWEST ones (Start of list).
        // The "Top Balance" of the current page = Opening + TotalInView - (Sum of Newer Items)
        
        $skippedSum = 0;
        if ($transactions->currentPage() > 1) {
            $skip = ($transactions->currentPage() - 1) * $transactions->perPage();
            // We need to sum the first $skip items of the query.
            // Efficient way: re-run query with limit/take.
            $skippedSum = (clone $query)
                ->latest('tanggal')->latest('id')
                ->take($skip)
                ->get() // We have to get() to sum in PHP to be accurate with ordering or use subquery
                ->sum(function($t) {
                    $net = $t->amount - ($t->withholding_pph23 ?? 0) - ($t->admin_fee ?? 0);
                    return $t->jenis === 'cash_in' ? $net : -$net;
                });
        }

        // Balance at the very top of the current page list (Latest date on this page)
        // Logic: End Balance (Latest) = Opening + Total Movement.
        // Balance at Top of Page = (Opening + TotalInView) - SkippedSum (Newer items not shown).
        $pageStartBalance = $openingBalance + $totalInView - $skippedSum;

        return view('cash-banks.index', compact('transactions', 'accounts', 'summary', 'pageStartBalance'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $accounts = CashBankAccount::orderBy('name')->get();
        
        // Eager load customer dan items untuk invoices
        $invoices = Invoice::with([
            'customer:id,name',
            'items:id,invoice_id,description'
        ])
        ->select('id', 'invoice_number', 'customer_id', 'subtotal', 'total_amount', 'paid_amount', 'pph23_amount', 'invoice_date', 'status')
        ->where('status', '!=', 'paid') // Exclude fully paid
        ->whereRaw('paid_amount < total_amount') // Only with outstanding balance
        ->latest()
        ->get();
        
        // Eager load vendor dan relasi untuk vendor bills
        $vendorBills = VendorBill::with([
            'vendor:id,name',
            'items.shipmentLeg.jobOrder:id,job_number'
        ])
        ->select('id', 'vendor_bill_number', 'vendor_id', 'total_amount', 'amount_paid', 'pph23', 'bill_date', 'status')
        ->where('status', '!=', 'paid') // Exclude fully paid
        ->whereRaw('amount_paid < total_amount') // Only with outstanding balance
        ->latest()
        ->get();
        
        // Eager load driver advances that are ready to be paid via Kas/Bank.
        // Payment Request is OPTIONAL - only as approval helper, not mandatory
        // Rule: Status pending / dp_paid (belum settled)
        $driverAdvances = \App\Models\Operations\DriverAdvance::with([
            'driver:id,name',
            'shipmentLeg.jobOrder:id,job_number,origin,destination,customer_id',
            'shipmentLeg.jobOrder.customer:id,name',
            'shipmentLeg.jobOrder.items:id,job_order_id,quantity,cargo_type',
            'shipmentLeg.truck:id,plate_number',
            'shipmentLeg.mainCost:id,shipment_leg_id,uang_jalan,driver_savings_deduction,driver_guarantee_deduction',
            'paymentRequests:id,driver_advance_id,amount,status'
        ])
        ->select('id', 'advance_number', 'driver_id', 'shipment_leg_id', 'amount', 'dp_amount', 'advance_date', 'status', 'notes',
                 'deduction_savings', 'deduction_guarantee')
        ->whereIn('status', ['pending', 'dp_paid'])
        ->latest()
        ->get();
        
        $coas = ChartOfAccount::orderBy('code')->get();

        $prefill = $request->only(['sumber', 'invoice_id', 'vendor_bill_id', 'amount']);

        // Handle Payment Request prefill
        if ($requestId = $request->get('payment_request_id')) {
            $paymentRequest = \App\Models\Operations\PaymentRequest::with(['vendor', 'driverAdvance.driver'])->find($requestId);
            if ($paymentRequest) {
                $prefill['amount'] = $paymentRequest->amount;
                $prefill['description'] = $paymentRequest->description;
                $prefill['payment_request_id'] = $paymentRequest->id;
                
                if ($paymentRequest->payment_type === 'vendor_bill') {
                    $prefill['sumber'] = 'vendor_payment';
                    $prefill['vendor_bill_id'] = $paymentRequest->vendor_bill_id;
                    $prefill['recipient_name'] = $paymentRequest->vendor->name ?? '';
                } elseif ($paymentRequest->payment_type === 'trucking') {
                    // Driver Advance
                    $prefill['sumber'] = 'other_out'; 
                    $prefill['recipient_name'] = $paymentRequest->driverAdvance->driver->name ?? '';
                } else {
                    // Manual
                    $prefill['sumber'] = 'other_out';
                    if ($paymentRequest->vendor_id) {
                         $prefill['sumber'] = 'vendor_payment';
                         $prefill['recipient_name'] = $paymentRequest->vendor->name ?? '';
                    }
                }
            }
        }

        return view('cash-banks.create', compact('accounts', 'invoices', 'vendorBills', 'driverAdvances', 'coas', 'prefill'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'cash_bank_account_id' => ['required', 'exists:cash_bank_accounts,id'],
            'tanggal' => ['required', 'date'],
            'jenis' => ['required', 'in:cash_in,cash_out'],
            'sumber' => ['required', 'in:customer_payment,vendor_payment,expense,other_in,other_out,driver_withdrawal,uang_jalan'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'invoice_ids' => ['nullable', 'array'],
            'invoice_ids.*' => ['exists:invoices,id'],
            'vendor_bill_id' => ['nullable', 'exists:vendor_bills,id'],
            'vendor_bill_ids' => ['nullable', 'array'],
            'vendor_bill_ids.*' => ['exists:vendor_bills,id'],
            'driver_advance_ids' => ['nullable', 'array'],
            'driver_advance_ids.*' => ['exists:driver_advances,id'],
            'coa_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'admin_fee' => ['nullable', 'numeric', 'min:0'],
            'withholding_pph23' => ['nullable', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'payment_request_id' => ['nullable', 'exists:payment_requests,id'],
        ]);

        $data['withholding_pph23'] = $data['withholding_pph23'] ?? 0;

        // Generate voucher number
        $voucherService = app(\App\Services\VoucherNumberService::class);

        // Handle multiple invoices
        if (!empty($data['invoice_ids']) && count($data['invoice_ids']) > 0) {
            $successCount = 0;
            $skippedCount = 0;
            $totalAmount = 0;
            $validInvoices = [];
            
            // Collect valid invoices and calculate total
            foreach ($data['invoice_ids'] as $invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if (!$invoice) continue;
                
                // Skip if already paid
                if ($invoice->status === 'paid') {
                    $skippedCount++;
                    continue;
                }
                
                $validInvoices[] = $invoice;
                $outstanding = $invoice->total_amount - $invoice->paid_amount;
                $totalAmount += $outstanding;
            }
            
            // Create ONE transaction for all invoices
            if (count($validInvoices) > 0) {
                // Generate single voucher number
                $voucherNumber = $voucherService->generate(
                    $data['cash_bank_account_id'],
                    $data['jenis'],
                    \Carbon\Carbon::parse($data['tanggal'])
                );
                
                // Create single cash bank transaction
                $trx = CashBankTransaction::create([
                    'voucher_number' => $voucherNumber,
                    'cash_bank_account_id' => $data['cash_bank_account_id'],
                    'tanggal' => $data['tanggal'],
                    'jenis' => $data['jenis'],
                    'sumber' => $data['sumber'],
                    'customer_id' => $validInvoices[0]->customer_id, // Use first customer
                    'amount' => $totalAmount,
                    'admin_fee' => $data['admin_fee'] ?? 0,
                    'withholding_pph23' => $data['withholding_pph23'] ?? 0,
                    'reference_number' => $data['reference_number'],
                    'recipient_name' => $data['recipient_name'],
                    'description' => $data['description'],
                ]);
                
                // Update each invoice status
                foreach ($validInvoices as $invoice) {
                    $outstanding = $invoice->total_amount - $invoice->paid_amount;
                    $newPaidAmount = $invoice->paid_amount + $outstanding;
                    
                    // Create payment record
                    \App\Models\Finance\InvoiceTransactionPayment::create([
                        'invoice_id' => $invoice->id,
                        'cash_bank_transaction_id' => $trx->id,
                        'amount_paid' => $outstanding,
                        'payment_date' => $data['tanggal'],
                        'notes' => $data['description'],
                    ]);

                    // Update invoice
                    if ($newPaidAmount >= $invoice->total_amount) {
                        $invoice->update([
                            'paid_amount' => $invoice->total_amount,
                            'status' => 'paid',
                            'paid_at' => now()
                        ]);
                    } else {
                        $invoice->update([
                            'paid_amount' => $newPaidAmount,
                            'status' => 'partial'
                        ]);
                    }
                    
                    $successCount++;
                }
                
                // Post journal (single entry for combined amount)
                if (class_exists('App\Services\Accounting\JournalService')) {
                    $svc = app('App\Services\Accounting\JournalService');
                    if ($data['sumber'] === 'customer_payment') {
                        $svc->postCustomerPayment($trx);
                    }
                }
            }

            $message = $successCount . ' invoices berhasil dibayar dengan 1 voucher.';
            if ($skippedCount > 0) {
                $message .= ' ' . $skippedCount . ' invoices dilewati (sudah lunas).';
            }

            return redirect()->route('cash-banks.index')->with('success', $message);
        }

        // Handle multiple vendor bills
        if (!empty($data['vendor_bill_ids']) && count($data['vendor_bill_ids']) > 0) {
            $successCount = 0;
            $skippedCount = 0;
            $totalAmount = 0;
            $validVendorBills = [];
            
            // Collect valid vendor bills and calculate total
            foreach ($data['vendor_bill_ids'] as $vendorBillId) {
                $vendorBill = VendorBill::find($vendorBillId);
                if (!$vendorBill) continue;
                
                // Skip if already paid
                if ($vendorBill->status === 'paid') {
                    $skippedCount++;
                    continue;
                }
                
                $validVendorBills[] = $vendorBill;
                $totalAmount += $vendorBill->total_amount;
            }
            
            // Create ONE transaction for all vendor bills
            if (count($validVendorBills) > 0) {
                // Generate single voucher number
                $voucherNumber = $voucherService->generate(
                    $data['cash_bank_account_id'],
                    $data['jenis'],
                    \Carbon\Carbon::parse($data['tanggal'])
                );
                
                // Create single cash bank transaction
                $trx = CashBankTransaction::create([
                    'voucher_number' => $voucherNumber,
                    'cash_bank_account_id' => $data['cash_bank_account_id'],
                    'tanggal' => $data['tanggal'],
                    'jenis' => $data['jenis'],
                    'sumber' => $data['sumber'],
                    'vendor_id' => $validVendorBills[0]->vendor_id, // Use first vendor
                    'amount' => $totalAmount,
                    'admin_fee' => $data['admin_fee'] ?? 0,
                    'withholding_pph23' => $data['withholding_pph23'] ?? 0,
                    'reference_number' => $data['reference_number'],
                    'recipient_name' => $data['recipient_name'],
                    'description' => $data['description'],
                ]);
                
                // Create payment records for each vendor bill
                foreach ($validVendorBills as $vendorBill) {
                    \App\Models\Finance\VendorBillPayment::create([
                        'vendor_bill_id' => $vendorBill->id,
                        'cash_bank_transaction_id' => $trx->id,
                        'amount_paid' => $vendorBill->total_amount,
                        'payment_date' => $data['tanggal'],
                        'notes' => $data['description'],
                    ]);
                    
                    // Update vendor bill amount_paid and status
                    $newAmountPaid = $vendorBill->amount_paid + $vendorBill->total_amount;
                    $outstandingBalance = $vendorBill->total_amount - $newAmountPaid;
                    
                    if ($outstandingBalance <= 0) {
                        $vendorBill->update([
                            'amount_paid' => $vendorBill->total_amount,
                            'status' => 'paid'
                        ]);
                    } else {
                        $vendorBill->update([
                            'amount_paid' => $newAmountPaid,
                            'status' => 'partially_paid'
                        ]);
                    }
                    
                    $successCount++;
                }
                
                // Post journal (single entry for combined amount)
                if (class_exists('App\Services\Accounting\JournalService')) {
                    $svc = app('App\Services\Accounting\JournalService');
                    if ($data['sumber'] === 'vendor_payment') {
                        $svc->postVendorPayment($trx);
                    }
                }
            }

            $message = $successCount . ' vendor bills berhasil dibayar dengan 1 voucher.';
            if ($skippedCount > 0) {
                $message .= ' ' . $skippedCount . ' vendor bills dilewati (sudah lunas).';
            }

            return redirect()->route('cash-banks.index')->with('success', $message);
        }

        // Handle multiple driver advances (uang jalan)
        if (!empty($data['driver_advance_ids']) && count($data['driver_advance_ids']) > 0) {
            $successCount = 0;
            $totalAmount = 0;
            $validDriverAdvances = [];
            $descriptions = [];
            
            // Collect valid driver advances and calculate total
            foreach ($data['driver_advance_ids'] as $advanceId) {
                $advance = \App\Models\Operations\DriverAdvance::with([
                    'driver',
                    'shipmentLeg.jobOrder.customer',
                    'shipmentLeg.truck',
                    'shipmentLeg.jobOrder.items',
                    'shipmentLeg.mainCost'
                ])->find($advanceId);
                
                if (!$advance) continue;
                
                $validDriverAdvances[] = $advance;
                
                // Calculate net amount (gross - deductions)
                $mainCost = $advance->shipmentLeg->mainCost;
                $grossAmount = $mainCost->uang_jalan ?? $advance->amount;
                $savingsDeduction = $mainCost->driver_savings_deduction ?? $advance->deduction_savings ?? 0;
                $guaranteeDeduction = $mainCost->driver_guarantee_deduction ?? $advance->deduction_guarantee ?? 0;
                $netAmount = $grossAmount - $savingsDeduction - $guaranteeDeduction;
                
                $totalAmount += $netAmount;
                
                // Generate description for this advance
                $leg = $advance->shipmentLeg;
                $jobOrder = $leg->jobOrder;
                $driver = $advance->driver;
                $truck = $leg->truck;
                $customer = $jobOrder->customer;
                
                // Get cargo details (first item)
                $cargoItem = $jobOrder->items->first();
                $cargoQty = $cargoItem ? $cargoItem->quantity : $leg->quantity;
                $cargoUnit = 'unit'; // Default unit since job_order_items doesn't have unit column
                $cargoDesc = $cargoItem ? $cargoItem->cargo_type : 'barang';
                
                // Determine if DP or full payment
                $paymentType = ($advance->dp_amount > 0 && $advance->status === 'dp_paid') ? 'Pelunasan' : 'DP';
                
                // Format: "Bayar [DP/Pelunasan] uang jalan [Driver] [Nopol] order [Customer] muat [Qty Unit Cargo] [Origin]-[Destination] [Job Number]"
                $desc = sprintf(
                    "Bayar %s uang jalan %s %s order %s muat %s %s %s %s-%s %s",
                    $paymentType,
                    $driver->name ?? 'N/A',
                    $truck->plate_number ?? 'N/A',
                    $customer->name ?? 'N/A',
                    $cargoQty,
                    $cargoUnit,
                    $cargoDesc,
                    $jobOrder->origin ?? 'N/A',
                    $jobOrder->destination ?? 'N/A',
                    $jobOrder->job_number
                );
                
                $descriptions[] = $desc;
            }
            
            // Create ONE transaction for all driver advances
            if (count($validDriverAdvances) > 0) {
                // Generate single voucher number
                $voucherNumber = $voucherService->generate(
                    $data['cash_bank_account_id'],
                    $data['jenis'],
                    \Carbon\Carbon::parse($data['tanggal'])
                );
                
                // Combine descriptions
                $combinedDescription = implode('; ', $descriptions);
                
                // Create single cash bank transaction
                $trx = CashBankTransaction::create([
                    'voucher_number' => $voucherNumber,
                    'cash_bank_account_id' => $data['cash_bank_account_id'],
                    'tanggal' => $data['tanggal'],
                    'jenis' => $data['jenis'],
                    'sumber' => 'uang_jalan',
                    'amount' => $totalAmount,
                    'admin_fee' => $data['admin_fee'] ?? 0,
                    'reference_number' => $data['reference_number'],
                    'recipient_name' => $data['recipient_name'],
                    'description' => $combinedDescription,
                ]);
                
                // Update each driver advance status
                foreach ($validDriverAdvances as $advance) {
                    $mainCost = $advance->shipmentLeg->mainCost;
                    $grossAmount = $mainCost->uang_jalan ?? $advance->amount;
                    $savingsDeduction = $mainCost->driver_savings_deduction ?? $advance->deduction_savings ?? 0;
                    $guaranteeDeduction = $mainCost->driver_guarantee_deduction ?? $advance->deduction_guarantee ?? 0;
                    $netAmount = $grossAmount - $savingsDeduction - $guaranteeDeduction;
                    
                    // Create payment record
                    \App\Models\Operations\DriverAdvancePayment::create([
                        'driver_advance_id' => $advance->id,
                        'cash_bank_transaction_id' => $trx->id,
                        'amount_paid' => $netAmount,
                        'payment_date' => $data['tanggal'],
                        'notes' => 'Pembayaran uang jalan',
                    ]);
                    
                    // Update driver advance
                    if ($advance->status === 'pending') {
                        // First payment (DP)
                        $advance->update([
                            'dp_amount' => $netAmount,
                            'dp_paid_date' => $data['tanggal'],
                            'status' => 'dp_paid'
                        ]);
                    } else {
                        // Full payment or additional payment
                        $newDpAmount = $advance->dp_amount + $netAmount;
                        $advance->update([
                            'dp_amount' => $newDpAmount,
                            'status' => ($newDpAmount >= $advance->amount) ? 'settled' : 'dp_paid'
                        ]);
                    }
                    
                    $successCount++;
                }
                
                // Post journal
                if (class_exists('App\Services\Accounting\JournalService')) {
                    $svc = app('App\Services\Accounting\JournalService');
                    $svc->postDriverAdvancePayment($trx);
                }
            }

            $message = $successCount . ' driver advance(s) berhasil dibayar dengan 1 voucher.';
            return redirect()->route('cash-banks.index')->with('success', $message);
        }

        // Single transaction (original flow)
        // Generate voucher number
        $data['voucher_number'] = $voucherService->generate(
            $data['cash_bank_account_id'],
            $data['jenis'],
            \Carbon\Carbon::parse($data['tanggal'])
        );
        
        $trx = CashBankTransaction::create($data);

        // Handle Payment Request update
        if (!empty($data['payment_request_id'])) {
            $pr = \App\Models\Operations\PaymentRequest::find($data['payment_request_id']);
            if ($pr) {
                $pr->update([
                    'status' => 'paid',
                    'paid_by' => auth()->id(),
                    'paid_at' => now(),
                    'cash_bank_transaction_id' => $trx->id
                ]);

                // If it's a Driver Advance (trucking), update the advance status
                if ($pr->payment_type === 'trucking' && $pr->driver_advance_id) {
                    $advance = $pr->driverAdvance;
                    $advance->update([
                        'dp_amount' => $advance->dp_amount + $pr->amount,
                        'dp_paid_date' => $data['tanggal'],
                        'status' => 'dp_paid'
                    ]);
                }
            }
        }

        // Auto-post journal
        if (class_exists('App\\Services\\Accounting\\JournalService')) {
            $svc = app('App\\Services\\Accounting\\JournalService');
            
            switch ($data['sumber']) {
                case 'customer_payment':
                    $svc->postCustomerPayment($trx);
                    break;
                case 'vendor_payment':
                    $svc->postVendorPayment($trx);
                    break;
                case 'expense':
                    $svc->postExpense($trx);
                    break;
                case 'other_in':
                    $svc->postOtherIncome($trx);
                    break;
                case 'other_out':
                case 'driver_withdrawal': // Post as other expense for now, or create specific method
                    $svc->postOtherExpense($trx);
                    break;
                case 'uang_jalan': // Driver advance payment (accrual basis)
                    $svc->postDriverAdvancePayment($trx);
                    break;
            }
        }

        if ($trx->invoice_id) {
            $inv = Invoice::find($trx->invoice_id);
            if ($inv) {
                $totalPaid = CashBankTransaction::where('invoice_id', $inv->id)
                    ->selectRaw('SUM(amount + COALESCE(withholding_pph23, 0)) as total')
                    ->value('total') ?? 0;

                if ($totalPaid >= $inv->total_amount) {
                    $inv->update(['status' => 'paid']);
                } elseif ($totalPaid > 0) {
                    $inv->update(['status' => 'partial']);
                }
            }
        }
        if ($trx->vendor_bill_id) {
            $bill = VendorBill::find($trx->vendor_bill_id);
            if ($bill && $data['amount'] >= $bill->total_amount) {
                $bill->update(['status' => 'paid']);
            } elseif ($bill) {
                $bill->update(['status' => 'partially_paid']);
            }
        }

        return redirect()->route('cash-banks.show', $trx)->with('success', 'Transaksi kas/bank dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CashBankTransaction $cash_bank)
    {
        $cash_bank->load(['account', 'invoice', 'vendorBill', 'customer', 'vendor', 'accountCoa']);

        return view('cash-banks.show', ['trx' => $cash_bank]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Print voucher
     */
    public function print(CashBankTransaction $cashBankTransaction)
    {
        return view('cash-banks.print', [
            'transaction' => $cashBankTransaction
        ]);
    }
    
    /**
     * Cancel/Void transaction with full rollback (SOFT DELETE - keeps audit trail)
     */
    public function cancel(CashBankTransaction $cashBankTransaction)
    {
        // Prevent voiding already voided transaction
        if ($cashBankTransaction->isVoided()) {
            return redirect()->back()->with('error', 'Transaksi sudah di-void sebelumnya.');
        }
        
        try {
            \DB::beginTransaction();
            
            // 1. Delete journal entries
            if (class_exists('App\Models\Accounting\Journal')) {
                $sourceType = $this->mapSourceTypeForJournal($cashBankTransaction->sumber);
                \App\Models\Accounting\Journal::where('source_type', $sourceType)
                    ->where('source_id', $cashBankTransaction->id)
                    ->delete();
            }
            
            // 2. Get all payment records
            $payments = \App\Models\Finance\VendorBillPayment::where('cash_bank_transaction_id', $cashBankTransaction->id)->get();
            
            // 3. Rollback vendor bill status and amount_paid
            foreach ($payments as $payment) {
                $vendorBill = $payment->vendorBill;
                if ($vendorBill) {
                    $newAmountPaid = $vendorBill->amount_paid - $payment->amount_paid;
                    
                    // Determine new status based on ENUM values
                    $newStatus = 'received'; // Default to received (not pending!)
                    if ($newAmountPaid > 0) {
                        $newStatus = 'partially_paid';
                    }
                    
                    $vendorBill->update([
                        'amount_paid' => max(0, $newAmountPaid),
                        'status' => $newStatus
                    ]);
                }
            }
            
            // 4. Delete payment records (hard delete - detail records)
            \App\Models\Finance\VendorBillPayment::where('cash_bank_transaction_id', $cashBankTransaction->id)->delete();
            
            // 5. Rollback invoice status if customer payment
            // 5. Rollback invoice status if customer payment
            // Get all invoice payments linked to this transaction
            $invoicePayments = \App\Models\Finance\InvoiceTransactionPayment::where('cash_bank_transaction_id', $cashBankTransaction->id)->get();
            
            if ($invoicePayments->count() > 0) {
                // Bulk payment rollback
                foreach ($invoicePayments as $payment) {
                    $invoice = $payment->invoice;
                    if ($invoice) {
                        $newPaidAmount = $invoice->paid_amount - $payment->amount_paid;
                        
                        // Determine new status
                        $newStatus = 'sent';
                        if ($newPaidAmount > 0) {
                            $newStatus = 'partial';
                        }
                        
                        $invoice->update([
                            'paid_amount' => max(0, $newPaidAmount),
                            'status' => $newStatus,
                            'paid_at' => $newStatus === 'paid' ? $invoice->paid_at : null
                        ]);
                    }
                }
                
                // Delete payment records
                \App\Models\Finance\InvoiceTransactionPayment::where('cash_bank_transaction_id', $cashBankTransaction->id)->delete();
                
            } elseif ($cashBankTransaction->invoice_id) {
                // Single invoice rollback (Legacy support)
                $invoice = Invoice::find($cashBankTransaction->invoice_id);
                if ($invoice) {
                    // Recalculate total paid after removing this transaction
                    $totalPaid = CashBankTransaction::where('invoice_id', $invoice->id)
                        ->where('id', '!=', $cashBankTransaction->id)
                        ->whereNull('voided_at') // Exclude voided transactions
                        ->selectRaw('SUM(amount + COALESCE(withholding_pph23, 0)) as total')
                        ->value('total') ?? 0;
                    
                    // Also include payments from InvoiceTransactionPayment table (excluding this trx)
                    $otherPayments = \App\Models\Finance\InvoiceTransactionPayment::where('invoice_id', $invoice->id)
                        ->where('cash_bank_transaction_id', '!=', $cashBankTransaction->id)
                        ->sum('amount_paid');
                        
                    $totalPaid += $otherPayments;
                    
                    if ($totalPaid >= $invoice->total_amount) {
                        $invoice->update(['paid_amount' => $invoice->total_amount, 'status' => 'paid']);
                    } elseif ($totalPaid > 0) {
                        $invoice->update(['paid_amount' => $totalPaid, 'status' => 'partial']);
                    } else {
                        $invoice->update(['paid_amount' => 0, 'status' => 'sent']);
                    }
                }
            }
            
            // 6. Rollback driver advance payments if uang_jalan
            if ($cashBankTransaction->sumber === 'uang_jalan') {
                $driverAdvancePayments = \App\Models\Operations\DriverAdvancePayment::where('cash_bank_transaction_id', $cashBankTransaction->id)->get();
                
                foreach ($driverAdvancePayments as $payment) {
                    $advance = $payment->driverAdvance;
                    if ($advance) {
                        // Rollback dp_amount
                        $newDpAmount = $advance->dp_amount - $payment->amount_paid;
                        
                        // Determine new status
                        $newStatus = 'pending';
                        if ($newDpAmount > 0) {
                            $newStatus = 'dp_paid';
                        }
                        
                        $advance->update([
                            'dp_amount' => max(0, $newDpAmount),
                            'status' => $newStatus,
                            'dp_paid_date' => $newStatus === 'pending' ? null : $advance->dp_paid_date
                        ]);
                    }
                }
                
                // Delete payment records
                \App\Models\Operations\DriverAdvancePayment::where('cash_bank_transaction_id', $cashBankTransaction->id)->delete();
            }
            
            // 7. VOID the transaction (SOFT DELETE - keeps record for audit)
            $cashBankTransaction->update([
                'voided_at' => now(),
                'voided_by' => auth()->id() ?? null,
                'void_reason' => 'Cancelled by user'
            ]);
            
            \DB::commit();
            
            return redirect()->route('cash-banks.index')->with('success', 'Transaksi berhasil di-VOID. Data tetap tersimpan untuk audit trail.');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to void transaction', [
                'transaction_id' => $cashBankTransaction->id,
                'voucher_number' => $cashBankTransaction->voucher_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
    
    /**
     * Map source type to journal source type
     */
    private function mapSourceTypeForJournal($sumber)
    {
        $map = [
            'customer_payment' => 'customer_payment',
            'vendor_payment' => 'vendor_payment',
            'expense' => 'expense',
            'other_in' => 'other_in',
            'other_out' => 'other_out',
            'uang_jalan' => 'uang_jalan', // Driver advance payment
        ];
        
        return $map[$sumber] ?? $sumber;
    }
}
