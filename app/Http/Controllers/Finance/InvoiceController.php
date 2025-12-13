<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Journal;
use App\Models\Finance\Invoice;
use App\Models\Master\Customer;
use App\Models\Operations\JobOrder;
use App\Models\Operations\Transport;
use App\Services\Accounting\JournalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    protected function authorizePermission(string $permission): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasPermission($permission)) {
            abort(403, 'Anda tidak memiliki izin untuk aksi ini.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('invoices.view');

        $query = Invoice::query()->with(['customer', 'items.jobOrder:id,job_number']);
        
        // Transaction type filter
        if ($transactionType = $request->get('transaction_type')) {
            $query->where('transaction_type', $transactionType);
        }
        
        // Invoice number search
        if ($invoiceNumber = $request->get('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $invoiceNumber . '%');
        }
        
        // Job Order search (search in related job orders)
        if ($jobOrder = $request->get('job_order')) {
            $query->whereHas('items.jobOrder', function($q) use ($jobOrder) {
                $q->where('job_number', 'like', '%' . $jobOrder . '%');
            });
        }
        
        // Status filter
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        
        // Customer filter
        if ($customer = $request->get('customer_id')) {
            $query->where('customer_id', $customer);
        }
        
        // Single date filter (replaces from/to for simplified filtering)
        if ($date = $request->get('date')) {
            $query->whereDate('invoice_date', $date);
        }
        
        // Legacy support for date range
        if ($from = $request->get('from')) {
            $query->whereDate('invoice_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('invoice_date', '<=', $to);
        }
        
        
        // Total amount filter (partial match like cash bank)
        if ($amountSearch = $request->get('min_amount')) {
            $val = preg_replace('/[^0-9]/', '', $amountSearch);
            if (is_numeric($val)) {
                $query->whereRaw("CAST(total_amount AS CHAR) LIKE ?", ["%{$val}%"]);
            }
        }
        
        // Approval status filter
        if ($approvalStatus = $request->get('approval_status')) {
            $query->where('approval_status', $approvalStatus);
        }
        
        // Tax invoice status filter
        if ($taxInvoiceStatus = $request->get('tax_invoice_status')) {
            $query->where('tax_invoice_status', $taxInvoiceStatus);
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
        $this->authorizePermission('invoices.create');

        $customers = Customer::orderBy('name')->get();
        
        // Get customer_id from request
        $customerId = $request->get('customer_id');
        
        // Get JO IDs that already have normal/final invoice (not cancelled)
        // These should be excluded from the list
        $fullyInvoicedJobOrderIds = \App\Models\Finance\InvoiceItem::query()
            ->whereNotNull('job_order_id')
            ->whereHas('invoice', function($q) {
                $q->where('status', '!=', 'cancelled')
                  ->whereIn('invoice_type', ['normal', 'final']);
            })
            ->pluck('job_order_id')
            ->unique()
            ->toArray();
        
        // Get job orders, filtered by customer if selected
        // Exclude JO that already have normal/final invoice
        $jobOrdersQuery = JobOrder::query()
            ->select('id', 'job_number', 'customer_id', 'origin', 'destination', 'status', 'invoice_amount')
            ->whereNotIn('id', $fullyInvoicedJobOrderIds);
        
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
        
        // Only eager load items for the modal list (needed for display)
        // Load shipmentLegs only for the first leg's load_date
        $jobOrders = $jobOrdersQuery
            ->with(['items:id,job_order_id,cargo_type,quantity,equipment_id', 
                    'items.equipment:id,name',
                    'shipmentLegs' => function($query) {
                        $query->select('id', 'job_order_id', 'load_date')
                              ->orderBy('load_date', 'asc')
                              ->limit(1);
                    }])
            ->latest()
            ->get();

        // AJAX request for job orders list
        if ($request->ajax() && $request->has('load_job_orders')) {
            // Get invoice status per JO (exclude cancelled invoices)
            $invoicedJobOrders = \App\Models\Finance\InvoiceItem::query()
                ->whereNotNull('job_order_id')
                ->whereHas('invoice', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->with(['invoice:id,invoice_number,invoice_type,status'])
                ->get()
                ->groupBy('job_order_id')
                ->map(function($items) {
                    $invoices = $items->pluck('invoice')->unique('id');
                    $hasDp = $invoices->where('invoice_type', 'down_payment')->isNotEmpty();
                    $hasNormalOrFinal = $invoices->whereIn('invoice_type', ['normal', 'final'])->isNotEmpty();
                    return [
                        'has_dp' => $hasDp,
                        'has_normal_or_final' => $hasNormalOrFinal,
                        'invoice_numbers' => $invoices->pluck('invoice_number')->implode(', '),
                    ];
                });
            
            return view('invoices.partials.job-order-list', [
                'jobOrders' => $jobOrders,
                'selectedJobOrderIds' => (array) $request->get('job_order_ids', []),
                'invoicedJobOrders' => $invoicedJobOrders,
            ]);
        }
        
        // AJAX request to load modal dynamically
        if ($request->ajax() && $request->has('load_modal')) {
            $selectedCustomer = Customer::find($customerId);
            if (!$selectedCustomer) {
                return response()->json(['error' => 'Customer not found'], 404);
            }
            
            // Get invoice status per JO (exclude cancelled invoices)
            $invoicedJobOrders = \App\Models\Finance\InvoiceItem::query()
                ->whereNotNull('job_order_id')
                ->whereHas('invoice', function($q) {
                    $q->where('status', '!=', 'cancelled');
                })
                ->with(['invoice:id,invoice_number,invoice_type,status'])
                ->get()
                ->groupBy('job_order_id')
                ->map(function($items) {
                    $invoices = $items->pluck('invoice')->unique('id');
                    $hasDp = $invoices->where('invoice_type', 'down_payment')->isNotEmpty();
                    $hasNormalOrFinal = $invoices->whereIn('invoice_type', ['normal', 'final'])->isNotEmpty();
                    return [
                        'has_dp' => $hasDp,
                        'has_normal_or_final' => $hasNormalOrFinal,
                        'invoice_numbers' => $invoices->pluck('invoice_number')->implode(', '),
                    ];
                });
            
            return view('invoices.partials.job-order-modal', [
                'selectedCustomer' => $selectedCustomer,
                'statusFilter' => $statusFilter,
                'jobOrders' => $jobOrders,
                'invoicedJobOrders' => $invoicedJobOrders,
            ]);
        }


        
        // AJAX request to fetch job order items as JSON
        if ($request->ajax() && $request->has('fetch_items')) {
            $selectedIds = explode(',', $request->get('job_order_ids', ''));
            $isDp = $request->boolean('is_dp');
            $dpAmount = $request->float('dp_amount');
            
            $items = [];
            
            foreach ($selectedIds as $jobOrderId) {
                // Load job order with all required fields
                $jobOrder = JobOrder::select('id', 'job_number', 'origin', 'destination', 'invoice_amount')
                    ->with([
                        'items:id,job_order_id,cargo_type,quantity,price,equipment_id',
                        'items.equipment:id,name',
                        'shipmentLegs.mainCost',
                        'shipmentLegs.additionalCosts',
                    ])
                    ->find($jobOrderId);
                    
                if (!$jobOrder) continue;
                
                if ($jobOrder->invoice_amount > 0) {
                    if ($isDp) {
                        // DP Invoice
                        $price = $dpAmount > 0 ? $dpAmount : ($jobOrder->invoice_amount * 0.5);
                        $items[] = [
                            'description' => "Uang Muka Job Order " . $jobOrder->job_number,
                            'quantity' => 1,
                            'unit_price' => $price,
                            'job_order_id' => $jobOrder->id,
                            'item_type' => 'job_order',
                            'exclude_tax' => false,
                        ];
                    } else {
                        // Normal Invoice
                        if ($jobOrder->items->count() > 0) {
                            foreach ($jobOrder->items as $joItem) {
                                $itemDesc = ($joItem->cargo_type ?? 'Item') . 
                                            ($joItem->equipment ? ' - ' . $joItem->equipment->name : '') .
                                            ' (' . $jobOrder->origin . ' → ' . $jobOrder->destination . ')';
                                            
                                $items[] = [
                                    'description' => $itemDesc,
                                    'quantity' => $joItem->quantity ?? 1,
                                    'unit_price' => $joItem->price ?? 0,
                                    'job_order_id' => $jobOrder->id,
                                    'item_type' => 'job_order',
                                    'exclude_tax' => false,
                                ];
                            }
                        } else {
                            // Legacy: Single line item
                            $items[] = [
                                'description' => $jobOrder->job_number . ' - ' . $jobOrder->origin . ' → ' . $jobOrder->destination,
                                'quantity' => 1,
                                'unit_price' => $jobOrder->invoice_amount ?? 0,
                                'job_order_id' => $jobOrder->id,
                                'item_type' => 'job_order',
                                'exclude_tax' => false,
                            ];
                        }
                    }
                }

                // Add billable shipment leg items after main items (skip for DP)
                if (!$isDp) {
                    foreach ($jobOrder->shipmentLegs as $leg) {
                        // Insurance premium billable
                        if ($leg->cost_category === 'asuransi' && $leg->mainCost && $leg->mainCost->premium_billable > 0) {
                            $items[] = [
                                'description' => 'Premi Asuransi - ' . $jobOrder->job_number . ' (Leg #' . $leg->leg_number . ')',
                                'quantity' => 1,
                                'unit_price' => $leg->mainCost->premium_billable,
                                'job_order_id' => $jobOrder->id,
                                'shipment_leg_id' => $leg->id,
                                'item_type' => 'insurance_billable',
                                'exclude_tax' => true,
                            ];
                        }

                        // Additional cost billables
                        foreach ($leg->additionalCosts as $additionalCost) {
                            if ($additionalCost->is_billable && $additionalCost->billable_amount > 0) {
                                $costTypeLabel = ucfirst(str_replace('_', ' ', $additionalCost->cost_type));
                                $items[] = [
                                    'description' => $costTypeLabel . ' - ' . $jobOrder->job_number . ' (Leg #' . $leg->leg_number . ')' . ($additionalCost->description ? ' - ' . $additionalCost->description : ''),
                                    'quantity' => 1,
                                    'unit_price' => $additionalCost->billable_amount,
                                    'job_order_id' => $jobOrder->id,
                                    'shipment_leg_id' => $leg->id,
                                    'item_type' => 'additional_cost_billable',
                                    'exclude_tax' => true,
                                ];
                            }
                        }
                    }
                }
            }
            
            return response()->json(['items' => $items]);
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
                $jobOrder = JobOrder::with(['items.equipment', 'shipmentLegs.mainCost', 'shipmentLegs.additionalCosts', 'invoices.items'])->find($jobOrderId);
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
                    $jobOrder = JobOrder::with(['items.equipment', 'shipmentLegs.mainCost', 'shipmentLegs.additionalCosts'])->find($jobOrderId);
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
        $this->authorizePermission('invoices.create');

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
            'invoice_type' => ['nullable', 'string', 'in:normal,down_payment,progress,final'],
            'is_dp' => ['nullable', 'boolean'], // Legacy support dari form
            'items' => ['array'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.job_order_id' => ['nullable', 'exists:job_orders,id'],
            'items.*.transport_id' => ['nullable', 'exists:transports,id'],
            'items.*.shipment_leg_id' => ['nullable', 'exists:shipment_legs,id'],
            'items.*.exclude_tax' => ['nullable', 'boolean'],
        ]);

        // Tentukan invoice_type
        $invoiceType = $data['invoice_type'] ?? 'normal';
        
        // Legacy support: jika is_dp=true, override invoice_type
        if (!empty($data['is_dp'])) {
            $invoiceType = 'down_payment';
        }

        $inv = new Invoice;
        $inv->fill([
            'customer_id' => $data['customer_id'],
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'status' => $data['status'] ?? 'draft',
            'invoice_type' => $invoiceType,
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
        $this->authorizePermission('invoices.view');

        $invoice->load(['customer', 'items.jobOrder:id,job_number']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Generate PDF/Print view for the invoice.
     */
    public function pdf(Invoice $invoice)
    {
        $this->authorizePermission('invoices.view');

        $invoice->load(['customer', 'items', 'createdBy']);
        
        // Check if invoice is approved for printing
        $isDraft = !$invoice->canBePrinted();
        
        return view('invoices.pdf', compact('invoice', 'isDraft'));
    }

    /**
     * Submit invoice for approval.
     */
    public function submitForApproval(Invoice $invoice)
    {
        $this->authorizePermission('invoices.submit');

        if (!$invoice->canBeSubmittedForApproval()) {
            return back()->with('error', 'Invoice tidak dapat diajukan untuk approval. Pastikan invoice memiliki items dan total amount > 0.');
        }

        $invoice->update([
            'approval_status' => 'pending_approval'
        ]);

        // Load relationships for notification
        $invoice->load(['customer', 'createdBy']);

        // Send notification to all super admins
        $superAdmins = \App\Models\User::where('role', 'super_admin')->where('is_active', true)->get();
        foreach ($superAdmins as $admin) {
            $admin->notify(new \App\Notifications\InvoiceSubmittedForApproval($invoice));
        }

        return back()->with('success', 'Invoice berhasil diajukan untuk approval dan notifikasi telah dikirim ke Super Admin.');
    }

    /**
     * Approve invoice.
     */
    public function approve(Invoice $invoice)
    {
        $this->authorizePermission('invoices.approve');

        if (!$invoice->canBeApproved()) {
            return back()->with('error', 'Invoice tidak dapat di-approve. Status approval saat ini: ' . $invoice->approval_status);
        }

        $invoice->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        return back()->with('success', 'Invoice berhasil di-approve dan siap untuk di-print.');
    }

    /**
     * Reject invoice.
     */
    public function reject(Request $request, Invoice $invoice)
    {
        $this->authorizePermission('invoices.approve');

        if (!$invoice->canBeApproved()) {
            return back()->with('error', 'Invoice tidak dapat di-reject. Status approval saat ini: ' . $invoice->approval_status);
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000']
        ]);

        $invoice->update([
            'approval_status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason
        ]);

        return back()->with('success', 'Invoice di-reject. Invoice dapat di-edit kembali.');
    }

    /**
     * Show revise invoice form.
     */
    public function revise(Invoice $invoice)
    {
        $this->authorizePermission('invoices.update');

        if (!$invoice->canBeRevised()) {
            return back()->with('error', 'Invoice tidak dapat direvisi. Status: ' . $invoice->approval_status);
        }
        
        $periodClosed = $invoice->isAccountingPeriodClosed();
        
        return view('invoices.revise', compact('invoice', 'periodClosed'));
    }

    /**
     * Store invoice revision.
     */
    public function storeRevision(Request $request, Invoice $invoice)
    {
        $this->authorizePermission('invoices.update');

        if (!$invoice->canBeRevised()) {
            return back()->with('error', 'Invoice tidak dapat direvisi.');
        }
        
        $validated = $request->validate([
            'revision_reason' => ['required', 'string', 'max:1000'],
            'confirm_period_closed' => ['sometimes', 'accepted'], // if period closed
        ]);
        
        // Check period closed
        if ($invoice->isAccountingPeriodClosed() && !$request->has('confirm_period_closed')) {
            return back()->withErrors(['confirm_period_closed' => 'Anda harus mengkonfirmasi bahwa sudah koordinasi dengan accounting.']);
        }
        
        // Handle journal based on period status
        $periodClosed = false;
        if ($invoice->journal_id) {
            try {
                $journalService = app(JournalService::class);
                
                // Check if period is closed
                $journal = Journal::find($invoice->journal_id);
                $periodClosed = $journal && $journal->period && $journal->period->status !== 'open';
                
                if ($periodClosed) {
                    // Period closed: Keep old journal, will create correction later
                    \Log::info("Invoice revision with closed period", [
                        'invoice_id' => $invoice->id,
                        'period' => $journal->period->month . '/' . $journal->period->year,
                        'action' => 'Will create correction journal when re-posted'
                    ]);
                } else {
                    // Period open: Delete old journal
                    $journalService->unpostInvoice($invoice);
                    \Log::info("Invoice revision - old journal deleted", [
                        'invoice_id' => $invoice->id,
                        'action' => 'Old journal deleted, will create new when re-posted'
                    ]);
                }
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal memproses jurnal: ' . $e->getMessage());
            }
        }
        
        // Update invoice for revision
        $invoice->update([
            // Increment revision number
            'revision_number' => $invoice->revision_number + 1,
            
            // Update invoice number with revision suffix
            'invoice_number' => $invoice->getNextRevisionNumber(),
            
            // Reset approval status to draft
            'approval_status' => 'draft',
            
            // Clear approval fields
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            
            // Set revision fields
            'revised_at' => now(),
            'revised_by' => auth()->id(),
            'revision_reason' => $validated['revision_reason'],
            
            // Keep original invoice reference (if first revision, set to self)
            'original_invoice_id' => $invoice->original_invoice_id ?? $invoice->id,
            
            // Keep journal_id if period closed, clear if period open
            'journal_id' => $periodClosed ? $invoice->journal_id : null,
        ]);
        
        return redirect()->route('invoices.edit', $invoice)
            ->with('success', 'Invoice berhasil direvisi. Status kembali ke draft. Silakan edit dan submit kembali untuk approval.');
    }

    /**
     * Revert invoice from sent back to draft.
     */
    public function revertToDraft(Invoice $invoice)
    {
        $this->authorizePermission('invoices.manage_status');

        if ($invoice->status !== 'sent' || $invoice->paid_amount > 0) {
            return back()->with('error', 'Invoice tidak dapat dikembalikan ke draft.');
        }

        // Delete related journal entries
        if (class_exists(JournalService::class)) {
            try {
                $invoice->journals()->delete();
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal menghapus jurnal: ' . $e->getMessage());
            }
        }

        $invoice->update(['status' => 'draft']);

        return back()->with('success', 'Invoice dikembalikan ke status draft dan jurnal dihapus.');
    }

    /**
     * Cancel invoice.
     */
    public function cancel(Invoice $invoice)
    {
        $this->authorizePermission('invoices.cancel');

        if (!$invoice->canBeCancelled()) {
            return back()->with('error', 'Invoice tidak dapat dibatalkan.');
        }

        $invoice->update(['status' => 'cancelled']);

        return back()->with('success', 'Invoice dibatalkan.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $this->authorizePermission('invoices.update');

        // Check if invoice can be edited
        if (!$invoice->canBeEdited()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Invoice cannot be edited because it is not in draft status.');
        }

        $customers = Customer::orderBy('name')->get();
        $invoice->load('items');

        // Prepare preview items from existing invoice items
        $previewItems = $invoice->items->map(function ($item) {
            return [
                'job_order_id' => $item->job_order_id,
                'shipment_leg_id' => $item->shipment_leg_id,
                'item_type' => $item->item_type ?? 'other',
                'exclude_tax' => $item->exclude_tax,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ];
        })->all();

        // Get job orders for the modal
        $statusFilter = request('status_filter', 'completed');
        $jobOrdersQuery = JobOrder::query()
            ->select('id', 'job_number', 'customer_id', 'origin', 'destination', 'status', 'invoice_amount')
            ->where('customer_id', $invoice->customer_id);

        if ($statusFilter === 'completed') {
            $jobOrdersQuery->where('status', 'completed');
        } elseif ($statusFilter === 'in_progress') {
            $jobOrdersQuery->where('status', 'in_progress');
        }

        $jobOrders = $jobOrdersQuery
            ->with(['items:id,job_order_id,cargo_type,quantity,equipment_id', 
                    'items.equipment:id,name',
                    'shipmentLegs' => function($query) {
                        $query->select('id', 'job_order_id', 'load_date')
                              ->orderBy('load_date', 'asc')
                              ->limit(1);
                    }])
            ->latest()
            ->get();

        return view('invoices.edit', compact('invoice', 'customers', 'previewItems', 'jobOrders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $this->authorizePermission('invoices.update');

        // Check if invoice can be edited
        if (!$invoice->canBeEdited()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Invoice cannot be edited because it is not in draft status.');
        }

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

        // Check if invoice_date is being changed to a closed period
        if ($data['invoice_date'] !== $invoice->invoice_date->format('Y-m-d')) {
            $newDate = new \DateTimeImmutable($data['invoice_date']);
            $period = \App\Models\Accounting\AccountingPeriod::where('year', $newDate->format('Y'))
                ->where('month', $newDate->format('n'))
                ->first();
            
            if ($period && $period->is_closed) {
                return back()->withErrors([
                    'invoice_date' => 'Tidak dapat mengubah tanggal invoice ke periode akuntansi yang sudah ditutup (' . $newDate->format('F Y') . '). Silakan pilih tanggal di periode yang masih terbuka.'
                ])->withInput();
            }
        }

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
        $this->authorizePermission('invoices.cancel');

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice dihapus.');
    }

    public function markAsSent(Invoice $invoice)
    {
        $this->authorizePermission('invoices.manage_status');

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
                $journalService = app(JournalService::class);
                
                // Check if this is a revised invoice
                if ($invoice->revision_number > 0) {
                    // Use repostInvoice for revised invoices (handles period-aware logic)
                    $journal = $journalService->repostInvoice($invoice);
                } else {
                    // Normal posting for new invoices
                    $journal = $journalService->postInvoice($invoice);
                }
                
                // Update journal_id
                $invoice->update(['journal_id' => $journal->id]);
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
        $this->authorizePermission('invoices.manage_status');

        $invoice->update(['status' => 'paid']);

        return back()->with('success', 'Invoice ditandai lunas.');
    }

    /**
     * Show invoice approval management page.
     */
    public function approvals(Request $request)
    {
        $this->authorizePermission('invoices.approve');

        $query = Invoice::query()->with(['customer', 'createdBy', 'approvedBy', 'rejectedBy']);

        // Filter by approval status
        $status = $request->get('status', 'pending_approval');
        if ($status && $status !== 'all') {
            $query->where('approval_status', $status);
        }

        // Filter by date range
        if ($from = $request->get('from')) {
            $query->whereDate('invoice_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('invoice_date', '<=', $to);
        }

        $invoices = $query->latest('invoice_date')->paginate(15)->withQueryString();

        // Count pending approvals for badge
        $pendingCount = Invoice::where('approval_status', 'pending_approval')->count();

        return view('invoices.approvals', compact('invoices', 'status', 'pendingCount'));
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
