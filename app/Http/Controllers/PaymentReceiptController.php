<?php

namespace App\Http\Controllers;

use App\Models\Finance\Invoice;
use App\Models\Finance\PaymentReceipt;
use App\Models\Finance\CashBankAccount;
use App\Models\Master\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentReceipt::with(['customer', 'receivedBy', 'bankAccount'])
            ->orderBy('payment_date', 'desc');
        
        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        
        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->to);
        }
        
        // Filter unallocated
        if ($request->has('unallocated')) {
            $query->whereRaw('amount > allocated_amount');
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $receipts = $query->paginate(20);
        
        // Summary
        $stats = [
            'total_received' => PaymentReceipt::sum('amount'),
            'total_allocated' => PaymentReceipt::sum('allocated_amount'),
            'total_unallocated' => PaymentReceipt::whereRaw('amount > allocated_amount')->sum(DB::raw('amount - allocated_amount')),
        ];
        
        $customers = Customer::orderBy('name')->get();
        
        return view('payment-receipts.index', compact('receipts', 'stats', 'customers'));
    }
    
    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $bankAccounts = CashBankAccount::where('is_active', true)->orderBy('name')->get();
        
        // Pre-select customer if passed
        $selectedCustomer = $request->customer_id 
            ? Customer::find($request->customer_id) 
            : null;
        
        // Get outstanding invoices for pre-selected customer
        $outstandingInvoices = $selectedCustomer
            ? Invoice::where('customer_id', $selectedCustomer->id)
                ->outstanding()
                ->orderBy('due_date')
                ->get()
            : collect();
        
        return view('payment-receipts.create', compact('customers', 'bankAccounts', 'selectedCustomer', 'outstandingInvoices'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:bank_transfer,cash,check,giro,other',
            'reference_number' => 'nullable|string|max:100',
            'bank_account_id' => 'nullable|exists:cash_bank_accounts,id',
            'notes' => 'nullable|string',
        ]);
        
        try {
            $receipt = PaymentReceipt::create($validated);
            
            return redirect()
                ->route('payment-receipts.show', $receipt)
                ->with('success', 'Payment receipt created successfully!');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create payment receipt: ' . $e->getMessage());
        }
    }
    
    public function show(PaymentReceipt $paymentReceipt)
    {
        $paymentReceipt->load(['customer', 'receivedBy', 'bankAccount', 'invoices']);
        
        // Get outstanding invoices for this customer
        $outstandingInvoices = Invoice::where('customer_id', $paymentReceipt->customer_id)
            ->outstanding()
            ->orderBy('due_date')
            ->get();
        
        return view('payment-receipts.show', compact('paymentReceipt', 'outstandingInvoices'));
    }
    
    public function destroy(PaymentReceipt $paymentReceipt)
    {
        // Can only delete if not allocated
        if ($paymentReceipt->allocated_amount > 0) {
            return back()->with('error', 'Cannot delete payment receipt that has been allocated to invoices!');
        }
        
        try {
            $paymentReceipt->delete();
            return redirect()
                ->route('payment-receipts.index')
                ->with('success', 'Payment receipt deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete payment receipt: ' . $e->getMessage());
        }
    }
    
    public function allocate(Request $request, PaymentReceipt $paymentReceipt)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
        ]);
        
        $invoice = Invoice::findOrFail($validated['invoice_id']);
        
        // Validation
        if ($invoice->customer_id !== $paymentReceipt->customer_id) {
            return back()->with('error', 'Invoice and payment receipt must belong to the same customer!');
        }
        
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice is already fully paid!');
        }
        
        $availableAmount = $paymentReceipt->amount - $paymentReceipt->allocated_amount;
        if ($validated['amount'] > $availableAmount) {
            return back()->with('error', 'Allocation amount exceeds available unallocated amount!');
        }
        
        $invoiceOutstanding = $invoice->total_amount - $invoice->paid_amount;
        if ($validated['amount'] > $invoiceOutstanding) {
            return back()->with('error', 'Allocation amount exceeds invoice outstanding amount!');
        }
        
        try {
            $paymentReceipt->allocateToInvoice($invoice, $validated['amount']);
            
            return back()->with('success', 'Payment allocated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to allocate payment: ' . $e->getMessage());
        }
    }
    
    public function deallocate(Request $request, PaymentReceipt $paymentReceipt)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);
        
        $invoice = Invoice::findOrFail($validated['invoice_id']);
        
        try {
            $paymentReceipt->deallocateFromInvoice($invoice);
            
            return back()->with('success', 'Payment deallocated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to deallocate payment: ' . $e->getMessage());
        }
    }
}
