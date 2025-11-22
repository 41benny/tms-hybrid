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
    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        
        // Get customer_id from request
        $customerId = $request->get('customer_id');
        
        // Get job orders, filtered by customer if selected
        $jobOrdersQuery = JobOrder::with(['items.equipment', 'shipmentLegs'])
            ->select('id', 'job_number', 'customer_id', 'origin', 'destination', 'status', 'invoice_amount');
        
        if ($customerId) {
            $jobOrdersQuery->where('customer_id', $customerId);
        }
        
        // Filter by status if requested
        $statusFilter = $request->get('status_filter', 'completed');
        if ($statusFilter === 'completed') {
            $jobOrdersQuery->where('status', 'completed');
        } elseif ($statusFilter === 'in_progress') {
            $jobOrdersQuery->where('status', 'in_progress');
        }
        
        $jobOrders = $jobOrdersQuery->latest()->get();

        // AJAX request for job orders list
        if ($request->ajax() && $request->has('load_job_orders')) {
            return view('invoices.partials.job-order-list', [
                'jobOrders' => $jobOrders,
                'selectedJobOrderIds' => (array) $request->get('job_order_ids', [])
            ]);
        }
        
        // Legacy support: also pass as $jobs
        $jobs = $jobOrders;
        
        $transports = Transport::select('id', 'job_order_id')->latest()->get();
        
        // Generate preview items if job orders are selected
        $previewItems = [];
        $billableItems = []; // Collect billable items separately
        $selectedJobOrderIds = (array) $request->get('job_order_ids', []);
        
        // DP Logic parameters
        $isDp = $request->boolean('is_dp');
        $dpAmount = $request->float('dp_amount');
        
        if (!empty($selectedJobOrderIds)) {
            // First pass: collect main invoice items
            foreach ($selectedJobOrderIds as $jobOrderId) {
                $jobOrder = JobOrder::with(['items', 'shipmentLegs.mainCost', 'shipmentLegs.additionalCosts', 'invoices.items'])->find($jobOrderId);
                if (!$jobOrder) continue;
                
                // Add main job order item (invoice_amount) - TAXABLE
                if ($jobOrder->invoice_amount > 0) {
                    // If DP, override description and amount
                    if ($isDp) {
                        $description = "Uang Muka Job Order " . $jobOrder->job_number;
                        $price = $dpAmount > 0 ? $dpAmount : ($jobOrder->invoice_amount / 2); // Default 50% if not specified? Or just full amount? Let's use passed amount or full if 0 (but user should specify)
                        // Actually if dpAmount is passed, it might be total for all selected jobs? 
                        // If multiple jobs selected for DP, how to split?
                        // Assumption: DP is usually for single JO or split evenly? 
                        // For simplicity, if multiple JOs, we might need logic. 
                        // But usually DP is per JO. Let's assume 1 JO for DP for now or apply amount to each?
                        // Better: if dp_amount is provided, use it. If not, maybe 50%?
                        // Let's stick to: if is_dp, use dp_amount if > 0, else 50% of invoice_amount.
                        if ($dpAmount <= 0) {
                            $price = $jobOrder->invoice_amount * 0.5;
                        } else {
                            // If multiple jobs, this is tricky. Let's assume 1 job for DP context usually.
                            // Or if multiple, we just use the amount as is (which might be wrong if it's total).
                            // Let's use the amount passed as the unit price for THIS item.
                            $price = $dpAmount; 
                        }

                        $previewItems[] = [
                            'description' => $description,
                            'quantity' => 1,
                            'unit_price' => $price,
                            'job_order_id' => $jobOrder->id,
                            'item_type' => 'job_order', // Still job_order type, but description differs
                            'exclude_tax' => false,
                        ];
                    } else {
                        // Normal Invoice
                        
                        // Check if JO has items with price (Itemized Invoicing)
                        if ($jobOrder->items->count() > 0) {
                            foreach ($jobOrder->items as $joItem) {
                                $itemDesc = ($joItem->cargo_type ?? 'Item') . 
                                            ($joItem->equipment ? ' - ' . $joItem->equipment->name : '') .
                                            ' (' . $jobOrder->origin . ' → ' . $jobOrder->destination . ')';
                                            
                                $previewItems[] = [
                                    'description' => $itemDesc,
                                    'quantity' => $joItem->quantity ?? 1,
                                    'unit_price' => $joItem->price ?? 0,
                                    'job_order_id' => $jobOrder->id,
                                    'item_type' => 'job_order',
                                    'exclude_tax' => false,
                                ];
                            }
                        } else {
                            // Legacy / Fallback: Single line item from total invoice_amount
                            $previewItems[] = [
                                'description' => $jobOrder->job_number . ' - ' . $jobOrder->origin . ' → ' . $jobOrder->destination,
                                'quantity' => 1,
                                'unit_price' => $jobOrder->invoice_amount ?? 0,
                                'job_order_id' => $jobOrder->id,
                                'item_type' => 'job_order',
                                'exclude_tax' => false, 
                            ];
                        }

                        // Check for Paid DPs to deduct (ONLY for Normal Invoice)
                        foreach ($jobOrder->invoices as $linkedInvoice) {
                            if ($linkedInvoice->status === 'cancelled' || $linkedInvoice->status === 'draft') continue;
                            
                            foreach ($linkedInvoice->items as $invItem) {
                                if ($invItem->job_order_id == $jobOrder->id && stripos($invItem->description, 'Uang Muka') !== false) {
                                    $previewItems[] = [
                                        'description' => 'Potongan Uang Muka (' . $linkedInvoice->invoice_number . ')',
                                        'quantity' => 1,
                                        'unit_price' => -1 * abs($invItem->amount),
                                        'job_order_id' => $jobOrder->id,
                                        'item_type' => 'dp_deduction',
                                        'exclude_tax' => false,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            
            // Second pass: collect billable items (will be added at the bottom)
            // SKIP billable items if creating DP
            if (!$isDp) {
                foreach ($selectedJobOrderIds as $jobOrderId) {
                    $jobOrder = JobOrder::with(['items', 'shipmentLegs.mainCost', 'shipmentLegs.additionalCosts'])->find($jobOrderId);
                    if (!$jobOrder) continue;
                    
                    // Collect billable items from shipment legs - NOT TAXABLE by default
                    foreach ($jobOrder->shipmentLegs as $leg) {
                        // Add insurance premium billable if exists
                        if ($leg->cost_category == 'asuransi' && $leg->mainCost && $leg->mainCost->premium_billable > 0) {
                            $billableItems[] = [
                                'description' => 'Premi Asuransi - ' . $jobOrder->job_number . ' (Leg #' . $leg->leg_number . ')',
                                'quantity' => 1,
                                'unit_price' => $leg->mainCost->premium_billable,
                                'job_order_id' => $jobOrder->id,
                                'shipment_leg_id' => $leg->id,
                                'item_type' => 'insurance_billable',
                                'exclude_tax' => true, // Insurance premium typically not taxed
                            ];
                        }
                        
                        // Add billable additional costs
                        foreach ($leg->additionalCosts as $additionalCost) {
                            if ($additionalCost->is_billable && $additionalCost->billable_amount > 0) {
                                $costTypeLabel = ucfirst(str_replace('_', ' ', $additionalCost->cost_type));
                                $billableItems[] = [
                                    'description' => $costTypeLabel . ' - ' . $jobOrder->job_number . ' (Leg #' . $leg->leg_number . ')' . 
                                                    ($additionalCost->description ? ' - ' . $additionalCost->description : ''),
                                    'quantity' => 1,
                                    'unit_price' => $additionalCost->billable_amount,
                                    'job_order_id' => $jobOrder->id,
                                    'shipment_leg_id' => $leg->id,
                                    'item_type' => 'additional_cost_billable',
                                    'exclude_tax' => true, // Additional costs typically not taxed
                                ];
                            }
                        }
                    }
                }
                // Merge: main items first, then billable items at the bottom
                $previewItems = array_merge($previewItems, $billableItems);
            }
        }
        
        return view('invoices.create', compact('customers', 'jobs', 'transports', 'jobOrders', 'previewItems'));
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
            'internal_notes' => ['nullable', 'string'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'pph23_amount' => ['nullable', 'numeric', 'min:0'],
            'show_pph23' => ['nullable', 'boolean'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'transaction_type' => ['nullable', 'string', 'in:01,02,03,04,05,06,07,08,09'],
            'items' => ['array'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.job_order_id' => ['nullable', 'exists:job_orders,id'],
            'items.*.transport_id' => ['nullable', 'exists:transports,id'],
            'items.*.shipment_leg_id' => ['nullable', 'exists:shipment_legs,id'],
            'items.*.exclude_tax' => ['nullable', 'boolean'],
        ]);

        $inv = new Invoice;
        $inv->fill([
            'customer_id' => $data['customer_id'],
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'status' => $data['status'] ?? 'draft',
            'notes' => $data['notes'] ?? null,
            'internal_notes' => $data['internal_notes'] ?? null,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'pph23_amount' => $data['pph23_amount'] ?? 0,
            'show_pph23' => $data['show_pph23'] ?? false,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'reference' => $data['reference'] ?? null,
            'transaction_type' => $data['transaction_type'] ?? '04',
        ]);
        $inv->invoice_number = $this->generateInvoiceNo($data['invoice_date']);
        $inv->save();

        $subtotal = 0;
        foreach ($data['items'] ?? [] as $row) {
            $qty = (float) $row['quantity'];
            $price = (float) $row['unit_price'];
            $amount = $qty * $price;
            $subtotal += $amount;
            
            $inv->items()->create([
                'description' => $row['description'],
                'quantity' => $qty,
                'unit_price' => $price,
                'amount' => $amount,
                'job_order_id' => $row['job_order_id'] ?? null,
                'transport_id' => $row['transport_id'] ?? null,
                'shipment_leg_id' => $row['shipment_leg_id'] ?? null,
                'exclude_tax' => $row['exclude_tax'] ?? false,
            ]);
        }
        
        $total = $subtotal + ($data['tax_amount'] ?? 0) - ($data['discount_amount'] ?? 0);
        $inv->update([
            'subtotal' => $subtotal,
            'total_amount' => $total
        ]);

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
     * Generate PDF/Print view for the invoice.
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'items', 'createdBy']);
        return view('invoices.pdf', compact('invoice'));
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
            'internal_notes' => ['nullable', 'string'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'pph23_amount' => ['nullable', 'numeric', 'min:0'],
            'show_pph23' => ['nullable', 'boolean'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'transaction_type' => ['nullable', 'string', 'in:01,02,03,04,05,06,07,08,09'],
            'items' => ['array'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.job_order_id' => ['nullable', 'exists:job_orders,id'],
            'items.*.transport_id' => ['nullable', 'exists:transports,id'],
            'items.*.shipment_leg_id' => ['nullable', 'exists:shipment_legs,id'],
            'items.*.exclude_tax' => ['nullable', 'boolean'],
        ]);

        $invoice->update([
            'customer_id' => $data['customer_id'],
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'internal_notes' => $data['internal_notes'] ?? null,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'pph23_amount' => $data['pph23_amount'] ?? 0,
            'show_pph23' => $data['show_pph23'] ?? false,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'reference' => $data['reference'] ?? null,
            'transaction_type' => $data['transaction_type'] ?? '04',
        ]);

        $invoice->items()->delete();
        $subtotal = 0;
        foreach ($data['items'] ?? [] as $row) {
            $qty = (float) $row['quantity'];
            $price = (float) $row['unit_price'];
            $amount = $qty * $price;
            $subtotal += $amount;
            
            $invoice->items()->create([
                'description' => $row['description'],
                'quantity' => $qty,
                'unit_price' => $price,
                'amount' => $amount,
                'job_order_id' => $row['job_order_id'] ?? null,
                'transport_id' => $row['transport_id'] ?? null,
                'shipment_leg_id' => $row['shipment_leg_id'] ?? null,
                'exclude_tax' => $row['exclude_tax'] ?? false,
            ]);
        }
        
        $total = $subtotal + ($data['tax_amount'] ?? 0) - ($data['discount_amount'] ?? 0);
        $invoice->update([
            'subtotal' => $subtotal,
            'total_amount' => $total
        ]);

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
        // Validasi total amount
        if ($invoice->total_amount <= 0) {
            return back()->with('error', 'Invoice tidak dapat diposting karena total amount = 0 atau negatif.');
        }

        // Validasi ada items
        if ($invoice->items()->count() === 0) {
            return back()->with('error', 'Invoice tidak dapat diposting karena tidak ada item.');
        }

        $invoice->update(['status' => 'sent']);

        if (class_exists(JournalService::class)) {
            try {
                app(JournalService::class)->postInvoice($invoice);
            } catch (\Exception $e) {
                // Rollback status jika posting gagal
                $invoice->update(['status' => 'draft']);
                return back()->with('error', 'Gagal membuat jurnal: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Invoice ditandai terkirim dan jurnal berhasil dibuat.');
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
