<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Master\Customer;
use App\Models\Master\Sales;
use App\Models\Operations\JobOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = JobOrder::query()->with(['customer', 'sales', 'shipmentLegs']);

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

        $viewMode = $request->get('view', 'table'); // default: table
        $orders = $query->latest()->paginate(15)->withQueryString();
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
            'items.*.serial_numbers' => ['nullable', 'string'],
        ]);

        $job = new JobOrder;
        $job->fill($data);
        $job->job_number = $this->generateJobNumber($data['order_date']);
        $job->status = 'draft';
        $job->save();

        // Save cargo items
        foreach ($data['items'] ?? [] as $itemData) {
            if (! empty($itemData['equipment_id']) || ! empty($itemData['cargo_type'])) {
                $item = new \App\Models\Operations\JobOrderItem($itemData);
                $item->job_order_id = $job->id;
                $item->save();
            }
        }

        return redirect()->route('job-orders.show', $job)->with('success', 'Job Order berhasil dibuat');
    }

    public function show(JobOrder $job_order)
    {
        $job_order->load([
            'customer',
            'sales',
            'items.equipment',
            'shipmentLegs.vendor',
            'shipmentLegs.truck',
            'shipmentLegs.driver',
            'shipmentLegs.mainCost',
            'shipmentLegs.additionalCosts',
        ]);

        return view('job-orders.show', ['job' => $job_order]);
    }

    public function edit(JobOrder $job_order)
    {
        $customers = Customer::orderBy('name')->get();
        $salesList = Sales::where('is_active', true)->orderBy('name')->get();
        $equipments = \App\Models\Master\Equipment::orderBy('name')->get();
        $job_order->load(['shipmentLegs', 'items']);

        return view('job-orders.edit', [
            'job' => $job_order,
            'customers' => $customers,
            'salesList' => $salesList,
            'equipments' => $equipments,
        ]);
    }

    public function update(Request $request, JobOrder $job_order)
    {
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
            'items.*.serial_numbers' => ['nullable', 'string'],
        ]);

        $job_order->update($data);

        // Update cargo items - delete old and recreate
        $job_order->items()->delete();
        foreach ($data['items'] ?? [] as $itemData) {
            if (! empty($itemData['equipment_id']) || ! empty($itemData['cargo_type'])) {
                $item = new \App\Models\Operations\JobOrderItem($itemData);
                $item->job_order_id = $job_order->id;
                $item->save();
            }
        }

        return redirect()->route('job-orders.show', $job_order)->with('success', 'Job Order berhasil diupdate');
    }

    public function destroy(JobOrder $job_order)
    {
        $job_order->delete();

        return redirect()->route('job-orders.index')->with('success', 'Job Order berhasil dihapus');
    }

    protected function generateJobNumber(string $date): string
    {
        $d = new \DateTimeImmutable($date);
        $prefix = 'JOB-'.$d->format('ymd').'-';
        $last = JobOrder::whereDate('order_date', $d->format('Y-m-d'))
            ->where('job_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('job_number');
        $seq = 1;
        if ($last && preg_match('/(\d{3})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}
