<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Invoice;
use App\Models\Master\Customer;
use App\Models\Operations\JobOrder;
use App\Models\Operations\Transport;
use App\Services\Accounting\JournalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::query()->with('customer');
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($customer = $request->get('customer_id')) {
            $query->where('customer_id', $customer);
        }
        if ($from = $request->get('from')) {
            $query->whereDate('invoice_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('invoice_date', '<=', $to);
        }
        $invoices = $query->latest()->paginate(15)->withQueryString();

        $customers = Customer::orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $jobs = JobOrder::select('id', 'job_number', 'customer_id')->latest()->get();
        $transports = Transport::select('id', 'job_order_id')->latest()->get();

        return view('invoices.create', compact('customers', 'jobs', 'transports'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'status' => ['nullable', Rule::in(['draft', 'sent', 'partially_paid', 'paid', 'cancelled'])],
            'notes' => ['nullable', 'string'],
            'items' => ['array'],
            'items.*.description' => ['required', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.job_order_id' => ['nullable', 'exists:job_orders,id'],
            'items.*.transport_id' => ['nullable', 'exists:transports,id'],
        ]);

        $inv = new Invoice;
        $inv->fill($data);
        $inv->invoice_number = $this->generateInvoiceNo($data['invoice_date']);
        $inv->status = $data['status'] ?? 'draft';
        $inv->save();

        $total = 0;
        foreach ($data['items'] ?? [] as $row) {
            $row['subtotal'] = (float) $row['qty'] * (float) $row['unit_price'];
            $total += $row['subtotal'];
            $inv->items()->create($row);
        }
        $inv->update(['total_amount' => $total]);

        return redirect()->route('invoices.show', $inv)->with('success', 'Invoice dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $customers = Customer::orderBy('name')->get();
        $invoice->load('items');

        return view('invoices.edit', compact('invoice', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'status' => ['required', Rule::in(['draft', 'sent', 'partially_paid', 'paid', 'cancelled'])],
            'notes' => ['nullable', 'string'],
            'items' => ['array'],
            'items.*.description' => ['required', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.job_order_id' => ['nullable', 'exists:job_orders,id'],
            'items.*.transport_id' => ['nullable', 'exists:transports,id'],
        ]);

        $invoice->update($data);

        $invoice->items()->delete();
        $total = 0;
        foreach ($data['items'] ?? [] as $row) {
            $row['subtotal'] = (float) $row['qty'] * (float) $row['unit_price'];
            $total += $row['subtotal'];
            $invoice->items()->create($row);
        }
        $invoice->update(['total_amount' => $total]);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice dihapus.');
    }

    public function markAsSent(Invoice $invoice)
    {
        $invoice->update(['status' => 'sent']);
        if (class_exists(JournalService::class)) {
            app(JournalService::class)->postInvoice($invoice);
        }

        return back()->with('success', 'Invoice ditandai terkirim.');
    }

    public function markAsPaid(Invoice $invoice)
    {
        $invoice->update(['status' => 'paid']);

        return back()->with('success', 'Invoice ditandai lunas.');
    }

    protected function generateInvoiceNo(string $date): string
    {
        $d = new \DateTimeImmutable($date);
        $prefix = 'INV-'.$d->format('Ym').'-';
        $last = Invoice::where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('invoice_number');
        $seq = 1;
        if ($last && preg_match('/(\d{4})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
