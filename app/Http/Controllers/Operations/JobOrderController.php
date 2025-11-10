<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Master\Customer;
use App\Models\Operations\JobOrder;
use App\Models\Operations\JobOrderItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = JobOrder::query()->with('customer');
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($customerId = $request->get('customer_id')) {
            $query->where('customer_id', $customerId);
        }
        if ($from = $request->get('from')) {
            $query->whereDate('order_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('order_date', '<=', $to);
        }
        $orders = $query->latest()->paginate(15)->withQueryString();

        $customers = Customer::orderBy('name')->get();

        return view('job-orders.index', compact('orders', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();

        return view('job-orders.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'order_date' => ['required', 'date'],
            'service_type' => ['required', Rule::in(['jpt', 'multi_moda', 'sewa_truk'])],
            'status' => ['nullable', Rule::in(['draft', 'confirmed', 'in_progress', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string'],
            'items' => ['array'],
            'items.*.equipment_id' => ['nullable', 'exists:equipments,id'],
            'items.*.equipment_name' => ['nullable', 'string'],
            'items.*.serial_number' => ['nullable', 'string'],
            'items.*.qty' => ['nullable', 'numeric', 'min:0.01'],
            'items.*.origin_route_id' => ['nullable', 'exists:routes,id'],
            'items.*.destination_route_id' => ['nullable', 'exists:routes,id'],
            'items.*.origin_text' => ['nullable', 'string'],
            'items.*.destination_text' => ['nullable', 'string'],
            'items.*.remark' => ['nullable', 'string'],
        ]);

        $job = new JobOrder;
        $job->fill($data);
        $job->job_number = $this->generateJoNumber($data['order_date']);
        $job->status = $data['status'] ?? 'draft';
        $job->save();

        foreach ($data['items'] ?? [] as $row) {
            $item = new JobOrderItem($row);
            $item->job_order_id = $job->id;
            $item->save();
        }

        return redirect()->route('job-orders.show', $job)->with('success', 'Job Order dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(JobOrder $job_order)
    {
        $job_order->load(['customer', 'items.equipment', 'items.originRoute', 'items.destinationRoute']);

        return view('job-orders.show', ['job' => $job_order]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobOrder $job_order)
    {
        $customers = Customer::orderBy('name')->get();
        $job_order->load('items');

        return view('job-orders.edit', ['job' => $job_order, 'customers' => $customers]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobOrder $job_order)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'order_date' => ['required', 'date'],
            'service_type' => ['required', Rule::in(['jpt', 'multi_moda', 'sewa_truk'])],
            'status' => ['required', Rule::in(['draft', 'confirmed', 'in_progress', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string'],
        ]);
        $job_order->update($data);

        return redirect()->route('job-orders.show', $job_order)->with('success', 'Job Order diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobOrder $job_order)
    {
        $job_order->delete();

        return redirect()->route('job-orders.index')->with('success', 'Job Order dihapus.');
    }

    protected function generateJoNumber(string $date): string
    {
        $d = new \DateTimeImmutable($date);
        $prefix = 'JO-'.$d->format('Ymd').'-';
        $last = JobOrder::whereDate('order_date', $d->format('Y-m-d'))
            ->where('job_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('job_number');
        $seq = 1;
        if ($last && preg_match('/(\d{4})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
