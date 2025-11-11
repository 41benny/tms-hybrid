<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Master\Driver;
use App\Models\Master\Truck;
use App\Models\Master\Vendor;
use App\Models\Operations\JobOrder;
use App\Models\Operations\LegAdditionalCost;
use App\Models\Operations\LegMainCost;
use App\Models\Operations\ShipmentLeg;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShipmentLegController extends Controller
{
    public function create(JobOrder $jobOrder)
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $trucks = Truck::where('is_active', true)->orderBy('plate_number')->get();
        $drivers = Driver::where('is_active', true)->orderBy('name')->get();

        return view('job-orders.legs.create', compact('jobOrder', 'vendors', 'trucks', 'drivers'));
    }

    public function store(Request $request, JobOrder $jobOrder)
    {
        $validated = $request->validate([
            'cost_category' => ['required', Rule::in(['trucking', 'vendor', 'pelayaran', 'asuransi'])],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'truck_id' => ['nullable', 'exists:trucks,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'vessel_name' => ['nullable', 'string', 'max:255'],
            'load_date' => ['required', 'date'],
            'unload_date' => ['required', 'date'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'serial_numbers' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            // Main costs - Vendor
            'vendor_cost' => ['nullable', 'numeric', 'min:0'],
            'ppn' => ['nullable', 'numeric', 'min:0'],
            'pph23' => ['nullable', 'numeric', 'min:0'],
            // Main costs - Trucking (Own Fleet)
            'uang_jalan' => ['nullable', 'numeric', 'min:0'],
            'bbm' => ['nullable', 'numeric', 'min:0'],
            'toll' => ['nullable', 'numeric', 'min:0'],
            'other_costs' => ['nullable', 'numeric', 'min:0'],
            // Main costs - Sea Freight
            'shipping_line' => ['nullable', 'string', 'max:255'],
            'freight_cost' => ['nullable', 'numeric', 'min:0'],
            'container_no' => ['nullable', 'string', 'max:100'],
            // Insurance costs
            'insurance_provider' => ['nullable', 'string', 'max:255'],
            'policy_number' => ['nullable', 'string', 'max:100'],
            'insured_value' => ['nullable', 'numeric', 'min:0'],
            'premium_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'premium_cost' => ['nullable', 'numeric', 'min:0'],
            'admin_fee' => ['nullable', 'numeric', 'min:0'],
            'billable_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'premium_billable' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Determine executor_type based on cost_category
        if ($validated['cost_category'] == 'trucking') {
            $validated['executor_type'] = 'own_fleet';
        } else {
            $validated['executor_type'] = 'vendor';
        }

        // Get next leg number (reset from 1 if no legs exist)
        $maxLegNumber = $jobOrder->shipmentLegs()->max('leg_number');
        $legNumber = $maxLegNumber ? $maxLegNumber + 1 : 1;

        // Create leg
        $leg = new ShipmentLeg($validated);
        $leg->job_order_id = $jobOrder->id;
        $leg->leg_number = $legNumber;
        $leg->leg_code = $this->generateLegCode();
        $leg->status = 'pending';
        $leg->save();

        // Create main costs based on category
        $costData = ['shipment_leg_id' => $leg->id];

        if ($validated['cost_category'] == 'trucking') {
            $costData['uang_jalan'] = $validated['uang_jalan'] ?? 0;
            $costData['bbm'] = $validated['bbm'] ?? 0;
            $costData['toll'] = $validated['toll'] ?? 0;
        } elseif ($validated['cost_category'] == 'vendor') {
            $costData['vendor_cost'] = $validated['vendor_cost'] ?? 0;
            $costData['ppn'] = $validated['ppn'] ?? 0;
            $costData['pph23'] = $validated['pph23'] ?? 0;
        } elseif ($validated['cost_category'] == 'pelayaran') {
            $costData['shipping_line'] = $validated['shipping_line'] ?? null;
            $costData['freight_cost'] = $validated['freight_cost'] ?? 0;
            $costData['ppn'] = $validated['ppn'] ?? 0;
            $costData['pph23'] = $validated['pph23'] ?? 0;
        } elseif ($validated['cost_category'] == 'asuransi') {
            $costData['insurance_provider'] = $validated['insurance_provider'] ?? null;
            $costData['policy_number'] = $validated['policy_number'] ?? null;
            $costData['insured_value'] = $validated['insured_value'] ?? 0;
            $costData['premium_rate'] = $validated['premium_rate'] ?? 0;
            $costData['premium_cost'] = $validated['premium_cost'] ?? 0;
            $costData['admin_fee'] = $validated['admin_fee'] ?? 0;
            $costData['billable_rate'] = $validated['billable_rate'] ?? 0;
            $costData['premium_billable'] = $validated['premium_billable'] ?? 0;
        }

        $mainCost = new LegMainCost($costData);
        $mainCost->save();

        return redirect()->route('job-orders.show', $jobOrder)->with('success', 'Leg berhasil ditambahkan');
    }

    public function edit(JobOrder $jobOrder, ShipmentLeg $leg)
    {
        $leg->load('mainCost');
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $trucks = Truck::where('is_active', true)->orderBy('plate_number')->get();
        $drivers = Driver::where('is_active', true)->orderBy('name')->get();

        return view('job-orders.legs.edit', compact('jobOrder', 'leg', 'vendors', 'trucks', 'drivers'));
    }

    public function update(Request $request, JobOrder $jobOrder, ShipmentLeg $leg)
    {
        $validated = $request->validate([
            'cost_category' => ['required', Rule::in(['trucking', 'vendor', 'pelayaran', 'asuransi'])],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'truck_id' => ['nullable', 'exists:trucks,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'vessel_name' => ['nullable', 'string', 'max:255'],
            'load_date' => ['required', 'date'],
            'unload_date' => ['required', 'date'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'serial_numbers' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['pending', 'in_transit', 'delivered', 'cancelled'])],
            'notes' => ['nullable', 'string'],
            // Main costs - Vendor
            'vendor_cost' => ['nullable', 'numeric', 'min:0'],
            'ppn' => ['nullable', 'numeric', 'min:0'],
            'pph23' => ['nullable', 'numeric', 'min:0'],
            // Main costs - Trucking (Own Fleet)
            'uang_jalan' => ['nullable', 'numeric', 'min:0'],
            'bbm' => ['nullable', 'numeric', 'min:0'],
            'toll' => ['nullable', 'numeric', 'min:0'],
            'other_costs' => ['nullable', 'numeric', 'min:0'],
            // Main costs - Sea Freight
            'shipping_line' => ['nullable', 'string', 'max:255'],
            'freight_cost' => ['nullable', 'numeric', 'min:0'],
            'container_no' => ['nullable', 'string', 'max:100'],
            // Insurance costs
            'insurance_provider' => ['nullable', 'string', 'max:255'],
            'policy_number' => ['nullable', 'string', 'max:100'],
            'insured_value' => ['nullable', 'numeric', 'min:0'],
            'premium_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'premium_cost' => ['nullable', 'numeric', 'min:0'],
            'admin_fee' => ['nullable', 'numeric', 'min:0'],
            'billable_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'premium_billable' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Determine executor_type based on cost_category
        if ($validated['cost_category'] == 'trucking') {
            $validated['executor_type'] = 'own_fleet';
        } else {
            $validated['executor_type'] = 'vendor';
        }

        $leg->update($validated);

        // Update main costs
        $leg->mainCost()->updateOrCreate(
            ['shipment_leg_id' => $leg->id],
            [
                'vendor_cost' => $validated['vendor_cost'] ?? 0,
                'ppn' => $validated['ppn'] ?? 0,
                'pph23' => $validated['pph23'] ?? 0,
                'uang_jalan' => $validated['uang_jalan'] ?? 0,
                'bbm' => $validated['bbm'] ?? 0,
                'toll' => $validated['toll'] ?? 0,
                'other_costs' => $validated['other_costs'] ?? 0,
                'shipping_line' => $validated['shipping_line'] ?? null,
                'freight_cost' => $validated['freight_cost'] ?? 0,
                'container_no' => $validated['container_no'] ?? null,
                'insurance_provider' => $validated['insurance_provider'] ?? null,
                'policy_number' => $validated['policy_number'] ?? null,
                'insured_value' => $validated['insured_value'] ?? 0,
                'premium_rate' => $validated['premium_rate'] ?? 0,
                'premium_cost' => $validated['premium_cost'] ?? 0,
                'admin_fee' => $validated['admin_fee'] ?? 0,
                'billable_rate' => $validated['billable_rate'] ?? 0,
                'premium_billable' => $validated['premium_billable'] ?? 0,
            ]
        );

        return redirect()->route('job-orders.show', $jobOrder)->with('success', 'Leg berhasil diupdate');
    }

    public function destroy(JobOrder $jobOrder, ShipmentLeg $leg)
    {
        $leg->delete();

        return redirect()->route('job-orders.show', $jobOrder)->with('success', 'Leg berhasil dihapus');
    }

    public function storeAdditionalCost(Request $request, ShipmentLeg $leg)
    {
        $validated = $request->validate([
            'cost_type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'is_billable' => ['nullable', 'boolean'],
            'billable_amount' => ['nullable', 'numeric', 'min:0'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
        ]);

        // Set default billable_amount = amount if is_billable is true
        if (($validated['is_billable'] ?? false) && empty($validated['billable_amount'])) {
            $validated['billable_amount'] = $validated['amount'];
        }

        $cost = new LegAdditionalCost($validated);
        $cost->shipment_leg_id = $leg->id;
        $cost->save();

        return back()->with('success', 'Biaya tambahan berhasil ditambahkan');
    }

    public function updateAdditionalCost(Request $request, LegAdditionalCost $cost)
    {
        $validated = $request->validate([
            'cost_type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'is_billable' => ['nullable', 'boolean'],
            'billable_amount' => ['nullable', 'numeric', 'min:0'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
        ]);

        // Set default billable_amount = amount if is_billable is true
        if (($validated['is_billable'] ?? false) && empty($validated['billable_amount'])) {
            $validated['billable_amount'] = $validated['amount'];
        }

        $cost->update($validated);

        return back()->with('success', 'Biaya tambahan berhasil diupdate');
    }

    public function destroyAdditionalCost(LegAdditionalCost $cost)
    {
        $cost->delete();

        return back()->with('success', 'Biaya tambahan berhasil dihapus');
    }

    public function getDriverByTruck(Request $request)
    {
        $truck = Truck::with('driver')->find($request->truck_id);

        return response()->json([
            'driver_id' => $truck?->driver_id,
            'driver_name' => $truck?->driver?->name,
        ]);
    }

    protected function generateLegCode(): string
    {
        $prefix = 'LEG-';
        $random = rand(10000, 99999);

        // Check if exists
        while (ShipmentLeg::where('leg_code', $prefix.$random)->exists()) {
            $random = rand(10000, 99999);
        }

        return $prefix.$random;
    }
}
