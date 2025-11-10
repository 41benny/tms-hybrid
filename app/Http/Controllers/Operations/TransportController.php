<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Master\Driver;
use App\Models\Master\Truck;
use App\Models\Master\Vendor;
use App\Models\Operations\JobOrder;
use App\Models\Operations\JobOrderItem;
use App\Models\Operations\Transport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Transport::query()->with(['jobOrder', 'truck', 'driver', 'vendor']);
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($executor = $request->get('executor_type')) {
            $query->where('executor_type', $executor);
        }
        if ($jobId = $request->get('job_order_id')) {
            $query->where('job_order_id', $jobId);
        }
        $transports = $query->latest()->paginate(15)->withQueryString();

        $jobs = JobOrder::select('id', 'job_number')->orderByDesc('id')->get();

        return view('transports.index', compact('transports', 'jobs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jobs = JobOrder::select('id', 'job_number')->orderByDesc('id')->get();
        $trucks = Truck::orderBy('plate_number')->get();
        $drivers = Driver::orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('transports.create', compact('jobs', 'trucks', 'drivers', 'vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'job_order_id' => ['required', 'exists:job_orders,id'],
            'job_order_item_id' => ['nullable', 'exists:job_order_items,id'],
            'executor_type' => ['required', Rule::in(['internal', 'vendor'])],
            'truck_id' => ['nullable', 'exists:trucks,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'departure_date' => ['nullable', 'date'],
            'arrival_date' => ['nullable', 'date', 'after_or_equal:departure_date'],
            'status' => ['nullable', Rule::in(['planned', 'on_route', 'delivered', 'closed', 'cancelled'])],
            'spj_number' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $t = new Transport;
        $t->fill($data);
        $t->status = $data['status'] ?? 'planned';
        $t->save();

        return redirect()->route('transports.show', $t)->with('success', 'Transport dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transport $transport)
    {
        $transport->load(['jobOrder', 'jobOrderItem', 'truck', 'driver', 'vendor', 'costs']);

        return view('transports.show', compact('transport'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transport $transport)
    {
        $transport->load('costs');
        $jobs = JobOrder::select('id', 'job_number')->orderByDesc('id')->get();
        $items = JobOrderItem::where('job_order_id', $transport->job_order_id)->get();
        $trucks = Truck::orderBy('plate_number')->get();
        $drivers = Driver::orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('transports.edit', compact('transport', 'jobs', 'items', 'trucks', 'drivers', 'vendors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transport $transport)
    {
        $data = $request->validate([
            'job_order_id' => ['required', 'exists:job_orders,id'],
            'job_order_item_id' => ['nullable', 'exists:job_order_items,id'],
            'executor_type' => ['required', Rule::in(['internal', 'vendor'])],
            'truck_id' => ['nullable', 'exists:trucks,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'departure_date' => ['nullable', 'date'],
            'arrival_date' => ['nullable', 'date', 'after_or_equal:departure_date'],
            'status' => ['required', Rule::in(['planned', 'on_route', 'delivered', 'closed', 'cancelled'])],
            'spj_number' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'costs' => ['array'],
            'costs.*.cost_category' => ['nullable', 'string'],
            'costs.*.description' => ['nullable', 'string'],
            'costs.*.amount' => ['nullable', 'numeric', 'min:0'],
            'costs.*.is_vendor_cost' => ['nullable', 'boolean'],
        ]);

        $transport->update($data);

        if (isset($data['costs'])) {
            $transport->costs()->delete();
            foreach ($data['costs'] as $row) {
                if (! isset($row['cost_category']) || ($row['amount'] ?? 0) == 0) {
                    continue;
                }
                $transport->costs()->create([
                    'cost_category' => $row['cost_category'],
                    'description' => $row['description'] ?? null,
                    'amount' => (float) ($row['amount'] ?? 0),
                    'is_vendor_cost' => (bool) ($row['is_vendor_cost'] ?? false),
                ]);
            }
        }

        return redirect()->route('transports.show', $transport)->with('success', 'Transport diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transport $transport)
    {
        $transport->delete();

        return redirect()->route('transports.index')->with('success', 'Transport dihapus.');
    }

    public function updateStatus(Request $request, Transport $transport)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['planned', 'on_route', 'delivered', 'closed', 'cancelled'])],
        ]);
        $transport->update(['status' => $data['status']]);

        return back()->with('success','Status diperbarui.');
    }
}
