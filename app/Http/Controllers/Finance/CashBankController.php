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
        if ($from = $request->get('from')) {
            $query->whereDate('tanggal', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('tanggal', '<=', $to);
        }
        $transactions = $query->latest('tanggal')->paginate(20)->withQueryString();

        $summary = [
            'in' => (clone $query)->where('jenis', 'cash_in')->sum('amount'),
            'out' => (clone $query)->where('jenis', 'cash_out')->sum('amount'),
        ];
        $summary['net'] = $summary['in'] - $summary['out'];

        return view('cash-banks.index', compact('transactions', 'accounts', 'summary'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $accounts = CashBankAccount::orderBy('name')->get();
        $invoices = Invoice::select('id', 'invoice_number', 'customer_id', 'total_amount')->latest()->get();
        $vendorBills = VendorBill::select('id', 'vendor_bill_number', 'vendor_id', 'total_amount')->latest()->get();
        $coas = ChartOfAccount::orderBy('code')->get();

        $prefill = $request->only(['sumber', 'invoice_id', 'vendor_bill_id', 'amount']);

        return view('cash-banks.create', compact('accounts', 'invoices', 'vendorBills', 'coas', 'prefill'));
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
            'sumber' => ['required', 'in:customer_payment,vendor_payment,expense,other_in,other_out'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'vendor_bill_id' => ['nullable', 'exists:vendor_bills,id'],
            'coa_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_number' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $trx = CashBankTransaction::create($data);

        if (class_exists('App\\Services\\Accounting\\JournalService')) {
            $svc = app('App\\Services\\Accounting\\JournalService');
            if ($data['sumber'] === 'customer_payment') {
                $svc->postCustomerPayment($trx);
            } elseif ($data['sumber'] === 'vendor_payment') {
                $svc->postVendorPayment($trx);
            } elseif ($data['sumber'] === 'expense') {
                $svc->postExpense($trx);
            }
        }

        if ($trx->invoice_id) {
            $inv = Invoice::find($trx->invoice_id);
            if ($inv && $data['amount'] >= $inv->total_amount) {
                $inv->update(['status' => 'paid']);
            } elseif ($inv) {
                $inv->update(['status' => 'partially_paid']);
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
}
