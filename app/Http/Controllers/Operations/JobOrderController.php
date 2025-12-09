<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Master\Customer;
use App\Models\Master\Sales;
use App\Models\Operations\JobOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JobOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = JobOrder::query()->with([
            'customer',
            'sales',
            'shipmentLegs.mainCost',
            'shipmentLegs.additionalCosts',
            'items.equipment',
            'invoiceItems',
        ]);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Sales user: restrict to their own job orders (by linked Sales record)
        if ($user && $user->role === User::ROLE_SALES) {
            $salesProfile = $user->salesProfile;
            if ($salesProfile) {
                $query->where('sales_id', $salesProfile->id);
            } else {
                // No linked sales profile => hide all JO for safety
                $query->whereRaw('1 = 0');
            }
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($customerId = $request->get('customer_id')) {
            $query->where('customer_id', $customerId);
        }
        if ($salesId = $request->get('sales_id')) {
            $query->where('sales_id', $salesId);
        }
        if ($serviceType = $request->get('service_type')) {
            $query->where('service_type', $serviceType);
        }
        if ($from = $request->get('from')) {
            $query->whereDate('order_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('order_date', '<=', $to);
        }
        
        // Filter by invoice status
        if ($invoiceStatus = $request->get('invoice_status')) {
            if ($invoiceStatus === 'not_invoiced') {
                $query->doesntHave('invoiceItems');
            } elseif ($invoiceStatus === 'invoiced') {
                $query->has('invoiceItems');
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at'); // default: created_at
        $sortOrder = $request->get('sort_order', 'desc'); // default: desc
        
        // Validate sort column
        $allowedSorts = ['job_number', 'order_date', 'created_at', 'updated_at', 'status'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        
        // Validate sort order
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $viewMode = $request->get('view', 'table'); // default: table
        $orders = $query->orderBy($sortBy, $sortOrder)->paginate(15)->withQueryString();
        $customers = Customer::orderBy('name')->get();
        $salesList = Sales::where('is_active', true)->orderBy('name')->get();

        return view('job-orders.index', compact('orders', 'customers', 'salesList', 'viewMode'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $salesList = Sales::where('is_active', true)->orderBy('name')->get();
        $equipments = \App\Models\Master\Equipment::orderBy('name')->get();

        return view('job-orders.create', compact('customers', 'salesList', 'equipments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'sales_id' => ['nullable', 'exists:sales,id'],
            'order_date' => ['required', 'date'],
            'service_type' => ['required', Rule::in(['multimoda', 'inland'])],
            'origin' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'invoice_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['array'],
            'items.*.equipment_id' => ['nullable', 'exists:equipments,id'],
            'items.*.cargo_type' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.01'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'items.*.serial_numbers' => ['nullable', 'string'],
        ]);

        $itemDataList = $request->input('items', null);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && $user->role === User::ROLE_SALES) {
            $salesProfile = $user->salesProfile;
            if ($salesProfile) {
                // Paksa JO yang dibuat sales selalu terikat ke profil sales-nya sendiri
                $data['sales_id'] = $salesProfile->id;
            }
        }

        unset($data['items']);

        // Wrap in transaction to prevent race condition
        $job = DB::transaction(function () use ($data, $itemDataList) {
            // Generate job number with lock INSIDE transaction
            $d = new \DateTimeImmutable($data['order_date']);
            $prefix = 'JOB-'.$d->format('ymd').'-';
            
            // Query by job_number prefix ONLY (not by order_date)
            // This prevents conflicts when order_date is edited after creation
            $last = JobOrder::where('job_number', 'like', $prefix.'%')
                ->lockForUpdate()  // Lock to prevent race condition
                ->orderByDesc('job_number')  // Order by job_number to get highest sequence
                ->value('job_number');
                
            $seq = 1;
            if ($last && preg_match('/(\d{3})$/', $last, $m)) {
                $seq = (int) $m[1] + 1;
            }
            
            $jobNumber = $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            
            // Create job order
            $job = new JobOrder;
            $job->fill($data);
            $job->job_number = $jobNumber;
            $job->status = 'draft';
            $job->save();

            // Save cargo items
            foreach ($itemDataList as $itemData) {
                if (! empty($itemData['equipment_id']) || ! empty($itemData['cargo_type'])) {
                    $item = new \App\Models\Operations\JobOrderItem($itemData);
                    $item->job_order_id = $job->id;
                    $item->save();
                }
            }

            return $job;
        });

        return redirect()
            ->route('job-orders.show', [$job, 'view' => $request->get('view')])
            ->with('success', 'Job Order berhasil dibuat');
    }

    public function show(JobOrder $job_order)
    {
        $this->ensureSalesCanAccess($job_order);

        $job_order->load([
            'customer',
            'sales',
            'items.equipment',
            'shipmentLegs.vendor',
            'shipmentLegs.vendor.activeBankAccounts',
            'shipmentLegs.truck',
            'shipmentLegs.driver',
            'shipmentLegs.mainCost',
            'shipmentLegs.additionalCosts',
            'shipmentLegs.driverAdvance',
            'shipmentLegs.vendorBillItems.vendorBill.paymentRequests',
        ]);

        return view('job-orders.show', ['job' => $job_order]);
    }

    public function edit(JobOrder $job_order)
    {
        $this->ensureSalesCanAccess($job_order);

        if ($job_order->isLocked()) {
            return back()->with('error', 'Job Order yang sudah completed atau cancelled tidak bisa di-edit');
        }

        $customers = Customer::orderBy('name')->get();
        $salesList = Sales::where('is_active', true)->orderBy('name')->get();
        $equipments = \App\Models\Master\Equipment::orderBy('name')->get();
        $job_order->load(['shipmentLegs', 'items.equipment']);

        return view('job-orders.edit', [
            'job' => $job_order,
            'customers' => $customers,
            'salesList' => $salesList,
            'equipments' => $equipments,
        ]);
    }

    public function update(Request $request, JobOrder $job_order)
    {
        $this->ensureSalesCanAccess($job_order);

        if ($job_order->isLocked()) {
            return back()->with('error', 'Job Order yang sudah completed atau cancelled tidak bisa di-edit');
        }

        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'sales_id' => ['nullable', 'exists:sales,id'],
            'order_date' => ['required', 'date'],
            'service_type' => ['required', Rule::in(['multimoda', 'inland'])],
            'origin' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'invoice_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'confirmed', 'in_progress', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string'],
            'items' => ['array'],
            'items.*.equipment_id' => ['nullable', 'exists:equipments,id'],
            'items.*.cargo_type' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.01'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'items.*.serial_numbers' => ['nullable', 'string'],
        ]);

        // Prevent change status dari cancelled/completed ke status lain
        if (in_array($job_order->status, ['completed', 'cancelled'])) {
            $data['status'] = $job_order->status; // Lock status
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && $user->role === User::ROLE_SALES) {
            $salesProfile = $user->salesProfile;
            if ($salesProfile) {
                // Sales hanya boleh meng-edit JO yang menjadi miliknya,
                // dan sales_id selalu dikunci ke profil sales tersebut.
                $data['sales_id'] = $salesProfile->id;
            }
        }

        $itemDataList = $data['items'] ?? [];
        unset($data['items']);

        $job_order->update($data);

        // Update cargo items - only if input dikirim
        if (is_array($itemDataList)) {
            $job_order->items()->delete();
            foreach ($itemDataList as $itemData) {
                if (! empty($itemData['equipment_id']) || ! empty($itemData['cargo_type'])) {
                    $item = new \App\Models\Operations\JobOrderItem($itemData);
                    $item->job_order_id = $job_order->id;
                    $item->save();
                }
            }
        }

        return redirect()
            ->route('job-orders.show', [$job_order, 'view' => $request->get('view')])
            ->with('success', 'Job Order berhasil diupdate');
    }

    public function destroy(Request $request, JobOrder $job_order)
    {
        $this->ensureSalesCanAccess($job_order);

        // Hanya superadmin yang bisa delete
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user?->isSuperAdmin()) {
            abort(403, 'Hanya superadmin yang bisa menghapus Job Order');
        }

        // Prevent delete jika ada transaksi finansial yang sudah dibayar
        $hasPaidDriverAdvances = $job_order->shipmentLegs()
            ->whereHas('driverAdvance', function ($q) {
                $q->whereIn('status', ['dp_paid', 'settled']);
            })
            ->exists();

        if ($hasPaidDriverAdvances) {
            return back()->with('error',
                'Tidak bisa menghapus Job Order karena ada Driver Advance yang sudah dibayar!'
            );
        }

        $hasPaidVendorBills = $job_order->shipmentLegs()
            ->whereHas('vendorBillItems.vendorBill', function ($q) {
                $q->whereIn('status', ['partially_paid', 'paid']);
            })
            ->exists();

        if ($hasPaidVendorBills) {
            return back()->with('error',
                'Tidak bisa menghapus Job Order karena ada Vendor Bill yang sudah dibayar!'
            );
        }

        $hasPaidInvoices = \App\Models\Finance\Invoice::whereHas('items', function ($q) use ($job_order) {
            $q->where('job_order_id', $job_order->id);
        })
            ->whereIn('status', ['partially_paid', 'paid'])
            ->exists();

        if ($hasPaidInvoices) {
            return back()->with('error',
                'Tidak bisa menghapus Job Order karena ada Invoice yang sudah dibayar!'
            );
        }

        // Safe to delete
        $job_order->delete();

        return redirect()
            ->route('job-orders.index', ['view' => $request->get('view', 'table')])
            ->with('success', 'Job Order berhasil dihapus');
    }

    public function cancel(Request $request, JobOrder $job_order)
    {
        $this->ensureSalesCanAccess($job_order);

        // Prevent cancel jika sudah completed atau cancelled
        if ($job_order->isLocked()) {
            return back()->with('error', 'Job Order sudah tidak bisa di-cancel');
        }

        // Prevent cancel jika ada transaksi finansial yang sudah dibayar
        $hasPaidDriverAdvances = $job_order->shipmentLegs()
            ->whereHas('driverAdvance', function ($q) {
                $q->whereIn('status', ['dp_paid', 'settled']);
            })
            ->exists();

        if ($hasPaidDriverAdvances) {
            return back()->with('error',
                'Tidak bisa cancel Job Order karena ada Driver Advance yang sudah dibayar!'
            );
        }

        $hasPaidVendorBills = $job_order->shipmentLegs()
            ->whereHas('vendorBillItems.vendorBill', function ($q) {
                $q->whereIn('status', ['partially_paid', 'paid']);
            })
            ->exists();

        if ($hasPaidVendorBills) {
            return back()->with('error',
                'Tidak bisa cancel Job Order karena ada Vendor Bill yang sudah dibayar!'
            );
        }

        $data = $request->validate([
            'cancel_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $job_order->update([
            'status' => 'cancelled',
            'cancel_reason' => $data['cancel_reason'],
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('job-orders.show', [$job_order, 'view' => $request->get('view')])
            ->with('success', 'Job Order berhasil di-cancel');
    }

    protected function generateJobNumber(string $date): string
    {
        $d = new \DateTimeImmutable($date);
        $prefix = 'JOB-'.$d->format('ymd').'-';
        
        // Use pessimistic locking to prevent race condition
        $last = JobOrder::whereDate('order_date', $d->format('Y-m-d'))
            ->where('job_number', 'like', $prefix.'%')
            ->lockForUpdate()  // Lock rows to prevent concurrent reads
            ->orderByDesc('id')
            ->value('job_number');
            
        $seq = 1;
        if ($last && preg_match('/(\d{3})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * For sales role, ensure the given job order belongs to their Sales profile.
     */
    protected function ensureSalesCanAccess(JobOrder $jobOrder): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || $user->role !== User::ROLE_SALES) {
            return;
        }

        // Jika JO belum di-assign ke sales manapun, izinkan sales melihatnya
        if ($jobOrder->sales_id === null) {
            return;
        }

        $salesProfile = $user->salesProfile;
        if (! $salesProfile || $jobOrder->sales_id !== $salesProfile->id) {
            abort(403, 'Anda tidak berhak mengakses Job Order ini.');
        }
    }
}
