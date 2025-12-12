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
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShipmentLegController extends Controller
{
    public function printTrucking(ShipmentLeg $leg)
    {
        $leg->load(['jobOrder.customer', 'mainCost', 'driver', 'truck']);

        if ($leg->cost_category !== 'trucking' || $leg->executor_type !== 'own_fleet') {
            return redirect()
                ->back()
                ->with('error', 'Print SPK Uang Jalan hanya untuk leg trucking own fleet.');
        }

        return view('job-orders.legs.print-trucking', compact('leg'));
    }

    public function create(JobOrder $jobOrder)
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $trucks = Truck::with('driver')->where('is_active', true)->orderBy('plate_number')->get();
        $drivers = Driver::where('is_active', true)->orderBy('name')->get();

        return view('job-orders.legs.create', compact('jobOrder', 'vendors', 'trucks', 'drivers'));
    }

    public function store(Request $request, JobOrder $jobOrder)
    {
        $validated = $request->validate([
            'cost_category' => ['required', Rule::in(['trucking', 'vendor', 'pelayaran', 'asuransi', 'pic'])],
            'vendor_id' => ['nullable', 'exists:vendors,id'], // Optional untuk trucking, required untuk kategori lainnya
            'truck_id' => ['nullable', 'exists:trucks,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'vessel_name' => ['nullable', 'string', 'max:255'],
            'load_date' => ['required', 'date'],
            'unload_date' => ['nullable', 'date'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'serial_numbers' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            // Main costs - Vendor
            'vendor_cost' => ['nullable', 'numeric', 'min:0'],
            'ppn' => ['nullable', 'numeric', 'min:0'],
            'pph23' => ['nullable', 'numeric', 'min:0'],
            // Main costs - Trucking (Own Fleet)
            'uang_jalan' => ['nullable', 'numeric', 'min:0'],
            'driver_savings_deduction' => ['nullable', 'numeric', 'min:0'],
            'driver_guarantee_deduction' => ['nullable', 'numeric', 'min:0'],
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
            // PIC costs
            'cost_type' => ['nullable', 'string', 'max:255'],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'pic_amount' => ['nullable', 'numeric', 'min:0'],
            'pic_notes' => ['nullable', 'string'],
            'ppn_noncreditable' => ['nullable', 'boolean'],
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

        // Create leg - only assign fields that belong to shipment_legs table
        $legData = [
            'cost_category' => $validated['cost_category'],
            'executor_type' => $validated['executor_type'],
            'vendor_id' => $validated['vendor_id'] ?? null,
            'truck_id' => $validated['truck_id'] ?? null,
            'driver_id' => $validated['driver_id'] ?? null,
            'vessel_name' => $validated['vessel_name'] ?? null,
            'load_date' => $validated['load_date'],
            'unload_date' => $validated['unload_date'] ?? null,
            'quantity' => $validated['quantity'],
            'serial_numbers' => $validated['serial_numbers'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        $leg = new ShipmentLeg($legData);
        $leg->job_order_id = $jobOrder->id;
        $leg->leg_number = $legNumber;
        $leg->leg_code = $this->generateLegCode();
        $leg->status = 'pending';
        $leg->save();

        // Create main costs based on category
        $costData = ['shipment_leg_id' => $leg->id];

        if ($validated['cost_category'] == 'trucking') {
            $costData['uang_jalan'] = $validated['uang_jalan'] ?? 0;
            $costData['driver_savings_deduction'] = $validated['driver_savings_deduction'] ?? 0;
            $costData['driver_guarantee_deduction'] = $validated['driver_guarantee_deduction'] ?? 0;
            $costData['bbm'] = $validated['bbm'] ?? 0;
            $costData['toll'] = $validated['toll'] ?? 0;
            $costData['other_costs'] = $validated['other_costs'] ?? 0;
        } elseif ($validated['cost_category'] == 'vendor') {
            $costData['vendor_cost'] = $validated['vendor_cost'] ?? 0;
            $costData['ppn'] = $validated['ppn'] ?? 0;
            $costData['pph23'] = $validated['pph23'] ?? 0;
            $costData['ppn_noncreditable'] = $request->boolean('ppn_noncreditable');
        } elseif ($validated['cost_category'] == 'pelayaran') {
            $costData['shipping_line'] = $validated['shipping_line'] ?? null;
            $costData['freight_cost'] = $validated['freight_cost'] ?? 0;
            $costData['ppn'] = $validated['ppn'] ?? 0;
            $costData['pph23'] = $validated['pph23'] ?? 0;
            $costData['ppn_noncreditable'] = $request->boolean('ppn_noncreditable');
            $costData['container_no'] = $validated['container_no'] ?? null;
        } elseif ($validated['cost_category'] == 'asuransi') {
            $costData['insurance_provider'] = $validated['insurance_provider'] ?? null;
            $costData['policy_number'] = $validated['policy_number'] ?? null;
            $costData['insured_value'] = $validated['insured_value'] ?? 0;
            $costData['premium_rate'] = $validated['premium_rate'] ?? 0;
            $costData['premium_cost'] = $validated['premium_cost'] ?? 0;
            $costData['admin_fee'] = $validated['admin_fee'] ?? 0;
            $costData['billable_rate'] = $validated['billable_rate'] ?? 0;
            $costData['premium_billable'] = $validated['premium_billable'] ?? 0;
        } elseif ($validated['cost_category'] == 'pic') {
            $costData['cost_type'] = $validated['cost_type'] ?? null;
            $costData['pic_name'] = $validated['pic_name'] ?? null;
            $costData['pic_phone'] = $validated['pic_phone'] ?? null;
            $costData['pic_amount'] = $validated['pic_amount'] ?? 0;
            $costData['pic_notes'] = $validated['pic_notes'] ?? null;
        }

        $mainCost = new LegMainCost($costData);
        $mainCost->save();

        // Auto-create Driver Advance for trucking (own fleet)
        if ($validated['cost_category'] === 'trucking' && $leg->driver_id) {
            $this->autoCreateDriverAdvance($leg);
        }

        // Auto-advance Job Order status from draft on first leg
        if ($jobOrder->status === 'draft' && $jobOrder->shipmentLegs()->count() === 1) {
            $jobOrder->update(['status' => 'in_progress']);
        }

        return redirect()->route('job-orders.show', $jobOrder)->with('success', 'Leg berhasil ditambahkan');
    }

    public function edit(JobOrder $jobOrder, ShipmentLeg $leg)
    {
        $leg->load('mainCost');
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $trucks = Truck::with('driver')->where('is_active', true)->orderBy('plate_number')->get();
        $drivers = Driver::where('is_active', true)->orderBy('name')->get();

        return view('job-orders.legs.edit', compact('jobOrder', 'leg', 'vendors', 'trucks', 'drivers'));
    }

    public function update(Request $request, JobOrder $jobOrder, ShipmentLeg $leg)
    {
        $validated = $request->validate([
            'cost_category' => ['required', Rule::in(['trucking', 'vendor', 'pelayaran', 'asuransi', 'pic'])],
            'vendor_id' => ['nullable', 'exists:vendors,id'], // Optional untuk trucking, required untuk kategori lainnya
            'truck_id' => ['nullable', 'exists:trucks,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'vessel_name' => ['nullable', 'string', 'max:255'],
              'load_date' => ['required', 'date'],
              'unload_date' => ['nullable', 'date'],
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
            'driver_savings_deduction' => ['nullable', 'numeric', 'min:0'],
            'driver_guarantee_deduction' => ['nullable', 'numeric', 'min:0'],
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
            // PIC costs
            'cost_type' => ['nullable', 'string', 'max:255'],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'pic_amount' => ['nullable', 'numeric', 'min:0'],
            'pic_notes' => ['nullable', 'string'],
            'ppn_noncreditable' => ['nullable', 'boolean'],
        ]);

          // Determine executor_type based on cost_category
          if ($validated['cost_category'] == 'trucking') {
              $validated['executor_type'] = 'own_fleet';
          } else {
              $validated['executor_type'] = 'vendor';
          }

          // Update only shipment_legs columns on the leg model
          $legData = [
              'cost_category' => $validated['cost_category'],
              'executor_type' => $validated['executor_type'],
              'vendor_id' => $validated['vendor_id'] ?? null,
              'truck_id' => $validated['truck_id'] ?? null,
              'driver_id' => $validated['driver_id'] ?? null,
              'vessel_name' => $validated['vessel_name'] ?? null,
              'load_date' => $validated['load_date'],
              'unload_date' => $validated['unload_date'] ?? null,
              'quantity' => $validated['quantity'],
              'serial_numbers' => $validated['serial_numbers'] ?? null,
              'status' => $validated['status'],
              'notes' => $validated['notes'] ?? null,
          ];

          $leg->update($legData);

        // Update main costs
        $leg->mainCost()->updateOrCreate(
            ['shipment_leg_id' => $leg->id],
            [
                'vendor_cost' => $validated['vendor_cost'] ?? 0,
                'ppn' => $validated['ppn'] ?? 0,
                'pph23' => $validated['pph23'] ?? 0,
                'uang_jalan' => $validated['uang_jalan'] ?? 0,
                'driver_savings_deduction' => $validated['driver_savings_deduction'] ?? 0,
                'driver_guarantee_deduction' => $validated['driver_guarantee_deduction'] ?? 0,
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
                'cost_type' => $validated['cost_type'] ?? null,
                'pic_name' => $validated['pic_name'] ?? null,
                'pic_phone' => $validated['pic_phone'] ?? null,
                'pic_amount' => $validated['pic_amount'] ?? 0,
                'pic_notes' => $validated['pic_notes'] ?? null,
                'ppn_noncreditable' => $request->boolean('ppn_noncreditable'),
            ]
        );

        // Sync Driver Advance for trucking category
        if ($validated['cost_category'] === 'trucking' && $leg->driver_id) {
            $this->syncDriverAdvance($leg);
        }

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
            'vendor_id' => ['nullable', 'exists:vendors,id'], // Optional, bisa kosong untuk trucking
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
            'vendor_id' => ['nullable', 'exists:vendors,id'], // Optional, bisa kosong untuk trucking
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

    /**
     * Get Job Order items for cargo selector modal.
     */
    public function getJobOrderItems(ShipmentLeg $leg)
    {
        $jobOrder = $leg->jobOrder;
        if (!$jobOrder) {
            return response()->json(['items' => [], 'qty_from_leg' => $leg->quantity]);
        }

        $items = $jobOrder->items()->with('equipment')->get();
        
        $formattedItems = $items->map(function ($item) {
            $name = $item->cargo_type ?: ($item->equipment?->name ?? 'Muatan');
            return [
                'id' => $item->id,
                'name' => $name,
                'quantity' => (float) $item->quantity,
                'label' => "{$name} ({$item->quantity} unit)",
            ];
        });

        // Add "Semua muatan" option if more than 1 item
        $allItemsOption = null;
        if ($items->count() > 1) {
            $totalQty = $items->sum('quantity');
            $allLabels = $items->map(function ($item) {
                $name = $item->cargo_type ?: ($item->equipment?->name ?? 'Muatan');
                return "{$name} ({$item->quantity} unit)";
            })->implode(' + ');
            
            $allItemsOption = [
                'id' => 'all',
                'name' => 'Semua muatan',
                'quantity' => $totalQty,
                'label' => $allLabels,
            ];
        }

        return response()->json([
            'items' => $formattedItems,
            'all_items_option' => $allItemsOption,
            'qty_from_leg' => $leg->quantity,
            'origin' => $jobOrder->origin,
            'destination' => $jobOrder->destination,
        ]);
    }

    public function generateVendorBill(Request $request, ShipmentLeg $leg)
    {
        // Validasi
        if (! $leg->vendor_id) {
            return back()->with('error', 'Leg ini tidak memiliki vendor yang ditunjuk');
        }

        if ($leg->status === 'cancelled') {
            return back()->with('error', 'Tidak bisa membuat vendor bill untuk leg yang dibatalkan');
        }

        $mainCost = $leg->mainCost;
        if (! $mainCost) {
            return back()->with('error', 'Leg ini tidak memiliki data biaya');
        }

        // Validate bill_mode parameter
        $billMode = $request->input('bill_mode', 'combined'); // default to 'combined'
        if (! in_array($billMode, ['combined', 'separate'])) {
            return back()->with('error', 'Mode billing tidak valid');
        }

        // Get cargo description and qty from request (from cargo selector modal)
        $cargoDescription = $request->input('cargo_description');
        $cargoQty = $request->input('cargo_qty', $leg->quantity);

        // Check for existing OPEN vendor bill for this Leg (Combined Mode Only for now)
        $existingBill = \App\Models\Finance\VendorBill::where('vendor_id', $leg->vendor_id)
            ->whereIn('status', ['draft', 'received'])
            ->whereHas('items', function ($q) use ($leg) {
                $q->where('shipment_leg_id', $leg->id);
            })
            ->latest()
            ->first();

        // Get additional costs
        $additionalCosts = $leg->additionalCosts()->get();

        if ($existingBill && $billMode === 'combined') {
            $this->updateCombinedBill($existingBill, $leg, $mainCost, $additionalCosts, $cargoDescription, $cargoQty);

            return redirect()->route('vendor-bills.show', $existingBill)
                ->with('success', "Vendor bill {$existingBill->vendor_bill_number} berhasil diperbarui (Mode: Gabung)!");
        }

        // Check total already generated vs leg total cost
        $totalGenerated = (float) $leg->vendorBillItems()->sum('subtotal');
        $legTotalCost = (float) $leg->total_cost;
        $remaining = $legTotalCost - $totalGenerated;

        if ($remaining <= 0) {
            return back()->with('error', 'Leg ini sudah fully billed. Total: Rp '.number_format($legTotalCost, 0, ',', '.'));
        }

        if ($billMode === 'combined') {
            // Mode GABUNG: 1 vendor bill untuk semua
            $bill = $this->createCombinedBill($leg, $mainCost, $additionalCosts, $cargoDescription, $cargoQty);

            return redirect()->route('vendor-bills.show', $bill)
                ->with('success', "Vendor bill {$bill->vendor_bill_number} berhasil dibuat (Mode: Gabung)!");
        } else {
            // Mode PISAH: 2 vendor bills
            $bills = $this->createSeparateBills($leg, $mainCost, $additionalCosts, $cargoDescription, $cargoQty);

            return redirect()->route('vendor-bills.index')
                ->with('success', count($bills)." vendor bill berhasil dibuat (Mode: Pisah)! Main Cost: {$bills[0]->vendor_bill_number}".
                    (isset($bills[1]) ? ", Additional Costs: {$bills[1]->vendor_bill_number}" : ''));
        }
    }

    /**
     * Quick flow khusus Sales:
     *  - Generate vendor bill (mode gabung) untuk Shipment Leg vendor/pelayaran/asuransi/PIC
     *  - Langsung redirect ke form Payment Request dengan vendor_bill_id tersebut.
     */
    public function salesQuickVendorRequest(Request $request, ShipmentLeg $leg)
    {
        $user = $request->user();

        if (! $user || $user->role !== User::ROLE_SALES) {
            abort(403, 'Hanya Sales yang dapat menggunakan fitur ini.');
        }

        if (! in_array($leg->cost_category, ['vendor', 'pelayaran', 'asuransi', 'pic'], true)) {
            return back()->with('error', 'Quick ajukan hanya untuk leg dengan vendor / pelayaran / asuransi / PIC.');
        }

        if (! $leg->vendor_id) {
            return back()->with('error', 'Leg ini tidak memiliki vendor yang ditunjuk.');
        }

        if ($leg->status === 'cancelled') {
            return back()->with('error', 'Tidak bisa membuat vendor bill untuk leg yang dibatalkan.');
        }

        // Jika sudah ada vendor bill items, minta user proses dari modul finance
        if ($leg->vendorBillItems()->exists()) {
            return back()->with('error', 'Leg ini sudah memiliki Vendor Bill. Ajukan pembayaran dari menu hutang / vendor bill.');
        }

        $mainCost = $leg->mainCost;
        if (! $mainCost) {
            return back()->with('error', 'Leg ini tidak memiliki data biaya utama.');
        }

        if ($leg->total_cost <= 0) {
            return back()->with('error', 'Total biaya leg masih 0. Lengkapi biaya sebelum mengajukan pembayaran.');
        }

        // Generate vendor bill (mode gabung) lalu langsung arahkan ke Payment Request
        $additionalCosts = $leg->additionalCosts()->get();
        $bill = $this->createCombinedBill($leg, $mainCost, $additionalCosts);

        return redirect()
            ->route('payment-requests.create', ['vendor_bill_id' => $bill->id])
            ->with('success', "Vendor bill {$bill->vendor_bill_number} dibuat. Silakan lengkapi pengajuan pembayaran.");
    }

    protected function createCombinedBill(ShipmentLeg $leg, LegMainCost $mainCost, $additionalCosts, ?string $cargoDescription = null, ?float $cargoQty = null)
    {
        $bill = \App\Models\Finance\VendorBill::create([
            'vendor_id' => $leg->vendor_id,
            'vendor_bill_number' => $this->generateBillNo(now()->toDateString()),
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            // Set langsung ke 'received' agar muncul di Pending Journal (unposted)
            'status' => 'received',
            'ppn_noncreditable' => (bool) ($mainCost->ppn_noncreditable ?? false),
            'notes' => "Auto-generated (Gabung) from Leg {$leg->leg_code} - Job Order {$leg->jobOrder->job_number}",
        ]);

        $totalAmount = 0;

        // Add all main cost items
        $totalAmount += $this->addMainCostItems($bill, $leg, $mainCost, $cargoDescription, $cargoQty);

        // Add all additional costs
        foreach ($additionalCosts as $cost) {
            $bill->items()->create([
                'shipment_leg_id' => $leg->id,
                'description' => "{$cost->cost_type} - {$cost->description}",
                'qty' => 1,
                'unit_price' => $cost->amount,
                'subtotal' => $cost->amount,
            ]);
            $totalAmount += $cost->amount;
        }

        // Add PPH23 (potong) at the end
        $totalAmount += $this->addPph23Item($bill, $leg, $mainCost);

        $bill->update(['total_amount' => $totalAmount]);

        return $bill;
    }

    protected function updateCombinedBill(\App\Models\Finance\VendorBill $bill, ShipmentLeg $leg, LegMainCost $mainCost, $additionalCosts, ?string $cargoDescription = null, ?float $cargoQty = null)
    {
        // 1. Delete items linked to this leg
        $bill->items()->where('shipment_leg_id', $leg->id)->delete();

        // 2. Add items
        $this->addMainCostItems($bill, $leg, $mainCost, $cargoDescription, $cargoQty);

        foreach ($additionalCosts as $cost) {
            $bill->items()->create([
                'shipment_leg_id' => $leg->id,
                'description' => "{$cost->cost_type} - {$cost->description}",
                'qty' => 1,
                'unit_price' => $cost->amount,
                'subtotal' => $cost->amount,
            ]);
        }

        // 3. Add PPH23
        $this->addPph23Item($bill, $leg, $mainCost);

        // 4. Recalculate Total
        $bill->update(['total_amount' => $bill->items()->sum('subtotal')]);

        return $bill;
    }

    protected function createSeparateBills(ShipmentLeg $leg, LegMainCost $mainCost, $additionalCosts, ?string $cargoDescription = null, ?float $cargoQty = null)
    {
        $bills = [];

        // Bill #1: Main Cost Only
        $mainBill = \App\Models\Finance\VendorBill::create([
            'vendor_id' => $leg->vendor_id,
            'vendor_bill_number' => $this->generateBillNo(now()->toDateString()),
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            // Set langsung ke 'received' agar muncul di Pending Journal (unposted)
            'status' => 'received',
            'ppn_noncreditable' => (bool) ($mainCost->ppn_noncreditable ?? false),
            'notes' => "Main Cost - Leg {$leg->leg_code} - Job Order {$leg->jobOrder->job_number}",
        ]);

        $mainTotalAmount = 0;
        $mainTotalAmount += $this->addMainCostItems($mainBill, $leg, $mainCost, $cargoDescription, $cargoQty);
        $mainTotalAmount += $this->addPph23Item($mainBill, $leg, $mainCost);

        $mainBill->update(['total_amount' => $mainTotalAmount]);
        $bills[] = $mainBill;

        // Bill #2: Additional Costs Only (jika ada)
        if ($additionalCosts->isNotEmpty()) {
            $addBill = \App\Models\Finance\VendorBill::create([
                'vendor_id' => $leg->vendor_id, // bisa NULL jika multiple vendors
                'vendor_bill_number' => $this->generateBillNo(now()->toDateString()),
                'bill_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                // Set langsung ke 'received' agar muncul di Pending Journal (unposted)
                'status' => 'received',
                'notes' => "Additional Costs - Leg {$leg->leg_code} - Job Order {$leg->jobOrder->job_number}",
            ]);

            $addTotalAmount = 0;
            foreach ($additionalCosts as $cost) {
                $addBill->items()->create([
                    'shipment_leg_id' => $leg->id,
                    'description' => "{$cost->cost_type} - {$cost->description} (Vendor: ".($cost->vendor->name ?? 'N/A').')',
                    'qty' => 1,
                    'unit_price' => $cost->amount,
                    'subtotal' => $cost->amount,
                ]);
                $addTotalAmount += $cost->amount;
            }

            $addBill->update(['total_amount' => $addTotalAmount]);
            $bills[] = $addBill;
        }

        return $bills;
    }

    protected function addMainCostItems($bill, ShipmentLeg $leg, LegMainCost $mainCost, ?string $cargoDescription = null, ?float $cargoQty = null): float
    {
        $totalAmount = 0;
        $jobOrder = $leg->jobOrder;
        $origin = $jobOrder?->origin ?? '-';
        $destination = $jobOrder?->destination ?? '-';
        
        // Build cargo description if not provided
        if (!$cargoDescription) {
            // Auto-detect from JO items
            $items = $jobOrder?->items ?? collect();
            if ($items->count() === 1) {
                $item = $items->first();
                $name = $item->cargo_type ?: ($item->equipment?->name ?? 'Muatan');
                $cargoDescription = "{$name} ({$item->quantity} unit)";
                $cargoQty = $cargoQty ?? $item->quantity;
            } elseif ($items->count() > 1) {
                // Multiple items - just use leg quantity
                $cargoDescription = "Muatan ({$leg->quantity} unit)";
                $cargoQty = $cargoQty ?? $leg->quantity;
            } else {
                $cargoDescription = "Muatan ({$leg->quantity} unit)";
                $cargoQty = $cargoQty ?? $leg->quantity;
            }
        }
        
        // Use cargo qty or leg qty
        $qty = $cargoQty ?? $leg->quantity;

        // Add Main Cost Items - Vendor
        if ($mainCost->vendor_cost > 0) {
            $bill->items()->create([
                'shipment_leg_id' => $leg->id,
                'description' => "Leg {$leg->leg_number} - Jasa angkut {$cargoDescription} dari {$origin} ke {$destination}",
                'qty' => $qty,
                'unit_price' => $mainCost->vendor_cost,
                'subtotal' => $mainCost->vendor_cost,
            ]);
            $totalAmount += $mainCost->vendor_cost;
        }

        // Add Main Cost Items - Freight (Pelayaran)
        if ($mainCost->freight_cost > 0) {
            $bill->items()->create([
                'shipment_leg_id' => $leg->id,
                'description' => "Leg {$leg->leg_number} - Jasa angkut laut {$cargoDescription} via {$mainCost->shipping_line}",
                'qty' => $qty,
                'unit_price' => $mainCost->freight_cost,
                'subtotal' => $mainCost->freight_cost,
            ]);
            $totalAmount += $mainCost->freight_cost;
        }

        // PPN - gunakan nilai yang sudah disimpan; jika 0, jangan auto-calc
        $ppnAmount = (float) ($mainCost->ppn ?? 0);

        if ($ppnAmount > 0) {
            $bill->items()->create([
                'shipment_leg_id' => $leg->id,
                'description' => "PPN 11% - Leg #{$leg->leg_number}",
                'qty' => 1,
                'unit_price' => $ppnAmount,
                'subtotal' => $ppnAmount,
            ]);
            $totalAmount += $ppnAmount;
        }

        if ($mainCost->premium_cost > 0) {
            $bill->items()->create([
                'shipment_leg_id' => $leg->id,
                'description' => "Insurance Premium - {$mainCost->insurance_provider} - {$cargoDescription}",
                'qty' => 1,
                'unit_price' => $mainCost->premium_cost,
                'subtotal' => $mainCost->premium_cost,
            ]);
            $totalAmount += $mainCost->premium_cost;
        }

        if ($mainCost->admin_fee > 0) {
            $bill->items()->create([
                'shipment_leg_id' => $leg->id,
                'description' => "Admin Fee - Leg #{$leg->leg_number}",
                'qty' => 1,
                'unit_price' => $mainCost->admin_fee,
                'subtotal' => $mainCost->admin_fee,
            ]);
            $totalAmount += $mainCost->admin_fee;
        }

        // Add PIC Cost
        if ($mainCost->pic_amount > 0) {
            $picType = ucfirst($mainCost->cost_type ?? 'PIC Payment');
            $picName = $mainCost->pic_name ? " - {$mainCost->pic_name}" : '';
            $bill->items()->create([
                'shipment_leg_id' => $leg->id,
                'description' => "{$picType}{$picName} - Leg #{$leg->leg_number}",
                'qty' => 1,
                'unit_price' => $mainCost->pic_amount,
                'subtotal' => $mainCost->pic_amount,
            ]);
            $totalAmount += $mainCost->pic_amount;
        }

        return $totalAmount;
    }

    protected function addPph23Item($bill, ShipmentLeg $leg, LegMainCost $mainCost): float
    {
        // PPH23 - gunakan nilai yang sudah disimpan; jika 0, jangan auto-calc
        $pph23Amount = (float) ($mainCost->pph23 ?? 0);

        if ($pph23Amount > 0) {
            $bill->items()->create([
                'shipment_leg_id' => $leg->id,
                'description' => "PPH 23 (Dipotong) - Leg #{$leg->leg_number}",
                'qty' => 1,
                'unit_price' => -$pph23Amount,
                'subtotal' => -$pph23Amount,
            ]);

            return -$pph23Amount;
        }

        return 0;
    }

    protected function generateBillNo(string $date): string
    {
        $d = new \DateTimeImmutable($date);
        $prefix = 'VBL-'.$d->format('Ym').'-';
        $last = \App\Models\Finance\VendorBill::where('vendor_bill_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('vendor_bill_number');
        $seq = 1;
        if ($last && preg_match('/(\d{4})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    protected function autoCreateDriverAdvance(ShipmentLeg $leg): void
    {
        // Only for own fleet with driver
        if ($leg->executor_type !== 'own_fleet' || ! $leg->driver_id) {
            return;
        }

        // Check if already exists
        if ($leg->driverAdvance()->exists()) {
            return;
        }

        $mainCost = $leg->mainCost;
        if (! $mainCost) {
            return;
        }

        // Calculate total advance amount
        $totalAdvance = $mainCost->uang_jalan + $mainCost->bbm + $mainCost->toll + $mainCost->other_costs;

        if ($totalAdvance <= 0) {
            return;
        }

        // Generate advance number
        $prefix = 'ADV-'.now()->format('Ym').'-';
        $last = \App\Models\Operations\DriverAdvance::where('advance_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('advance_number');
        $seq = 1;
        if ($last && preg_match('/(\d{4})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }
        $advanceNumber = $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

        // Create driver advance
        \App\Models\Operations\DriverAdvance::create([
            'shipment_leg_id' => $leg->id,
            'driver_id' => $leg->driver_id,
            'advance_number' => $advanceNumber,
            'advance_date' => now()->toDateString(),
            'amount' => $totalAdvance,
            'deduction_savings' => $mainCost->driver_savings_deduction ?? 0,
            'deduction_guarantee' => $mainCost->driver_guarantee_deduction ?? 0,
            'status' => 'pending',
            'notes' => "Auto-generated from Leg {$leg->leg_code} - Job Order {$leg->jobOrder->job_number}",
        ]);
    }

    /**
     * Sync Driver Advance amount when Shipment Leg costs are updated.
     * If DA exists, update amount and repost journal (if period is open).
     * If DA doesn't exist, create it.
     */
    protected function syncDriverAdvance(ShipmentLeg $leg): void
    {
        // Reload mainCost
        $leg->load('mainCost');
        $mainCost = $leg->mainCost;

        if (!$mainCost) {
            return;
        }

        // Calculate new total amount
        $newAmount = (float) ($mainCost->uang_jalan ?? 0)
            + (float) ($mainCost->bbm ?? 0)
            + (float) ($mainCost->toll ?? 0)
            + (float) ($mainCost->other_costs ?? 0);

        // Get existing Driver Advance
        $driverAdvance = $leg->driverAdvance;

        if (!$driverAdvance) {
            // Create new if doesn't exist
            if ($newAmount > 0) {
                $this->autoCreateDriverAdvance($leg);
            }
            return;
        }

        // Check if amount changed
        $oldAmount = (float) $driverAdvance->amount;
        if (abs($oldAmount - $newAmount) < 0.01) {
            // No change, skip
            return;
        }

        // Update DA amount
        $driverAdvance->update([
            'amount' => $newAmount,
            'deduction_savings' => $mainCost->driver_savings_deduction ?? 0,
            'deduction_guarantee' => $mainCost->driver_guarantee_deduction ?? 0,
        ]);

        // If journal is posted, try to repost with revision flag
        if ($driverAdvance->journal_status === 'posted' && $driverAdvance->journal_id) {
            try {
                $journalService = app(\App\Services\Accounting\JournalService::class);
                $journalService->repostDriverAdvance($driverAdvance);
            } catch (\Exception $e) {
                // Log error but don't fail the update
                \Log::warning('Failed to repost Driver Advance journal', [
                    'advance_id' => $driverAdvance->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
