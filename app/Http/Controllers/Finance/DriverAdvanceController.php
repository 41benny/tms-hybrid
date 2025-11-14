<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Operations\DriverAdvance;
use Illuminate\Http\Request;

class DriverAdvanceController extends Controller
{
    public function index(Request $request)
    {
        $query = DriverAdvance::query()->with(['driver', 'shipmentLeg.jobOrder']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($driver = $request->get('driver_id')) {
            $query->where('driver_id', $driver);
        }

        $advances = $query->latest()->paginate(20)->withQueryString();
        $drivers = \App\Models\Master\Driver::orderBy('name')->get();

        return view('driver-advances.index', compact('advances', 'drivers'));
    }

    public function show(DriverAdvance $driverAdvance)
    {
        $driverAdvance->load(['driver', 'shipmentLeg.jobOrder', 'shipmentLeg.mainCost']);

        return view('driver-advances.show', ['advance' => $driverAdvance]);
    }

    public function payDP(Request $request, DriverAdvance $driverAdvance)
    {
        $validated = $request->validate([
            'dp_amount' => ['required', 'numeric', 'min:0', 'max:'.$driverAdvance->amount],
            'dp_paid_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $driverAdvance->update([
            'dp_amount' => $validated['dp_amount'],
            'dp_paid_date' => $validated['dp_paid_date'],
            'status' => 'dp_paid',
            'notes' => $validated['notes'] ?? $driverAdvance->notes,
        ]);

        return redirect()->route('driver-advances.show', $driverAdvance)
            ->with('success', 'DP sebesar Rp '.number_format($validated['dp_amount'], 0, ',', '.').' berhasil dibayarkan ke driver');
    }

    public function processSettlement(Request $request, DriverAdvance $driverAdvance)
    {
        $validated = $request->validate([
            'deduction_savings' => ['nullable', 'numeric', 'min:0'],
            'deduction_guarantee' => ['nullable', 'numeric', 'min:0'],
            'settlement_date' => ['required', 'date'],
            'settlement_notes' => ['nullable', 'string'],
        ]);

        $remainingAmount = $driverAdvance->remaining_amount;
        $totalDeductions = ($validated['deduction_savings'] ?? 0) + ($validated['deduction_guarantee'] ?? 0);
        $finalPayment = $remainingAmount - $totalDeductions;

        if ($finalPayment < 0) {
            return back()->with('error', 'Total potongan tidak boleh melebihi sisa pembayaran');
        }

        $driverAdvance->update([
            'deduction_savings' => $validated['deduction_savings'] ?? 0,
            'deduction_guarantee' => $validated['deduction_guarantee'] ?? 0,
            'settlement_date' => $validated['settlement_date'],
            'settlement_notes' => $validated['settlement_notes'],
            'status' => 'settled',
            'paid_date' => $validated['settlement_date'], // Untuk backward compatibility
        ]);

        return redirect()->route('driver-advances.show', $driverAdvance)
            ->with('success', 'Pelunasan sebesar Rp '.number_format($finalPayment, 0, ',', '.').' berhasil diproses');
    }
}
