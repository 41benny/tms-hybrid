<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\VendorBill;
use App\Models\Operations\PaymentRequest;
use App\Models\Operations\DriverAdvance;
use App\Models\Operations\ShipmentLeg;
use Illuminate\Support\Facades\DB;

class HutangController extends Controller
{
    /**
     * Dashboard Hutang - Hanya untuk user dengan permission 'hutang'
     * Menampilkan overview semua hutang vendor
     */
    public function dashboard()
    {
        // Payables Metrics (Pengajuan Pembayaran)
        // Total nominal tagihan vendor (exclude cancelled)
        $totalVendorBills = VendorBill::query()->whereNotIn('status', ['cancelled'])->sum('total_amount');

        // Total sudah diajukan (pending, approved, paid) - exclude rejected/cancelled if any
        $totalRequested = PaymentRequest::query()
            ->whereNotIn('status', ['rejected'])
            ->sum('amount');

        // Remaining to request (sum sisa belum diajukan dari vendor bills outstanding)
        $outstandingBills = VendorBill::query()->outstanding()->with('paymentRequests')->get();
        $totalRemainingToRequest = $outstandingBills->sum(function ($bill) {
            return $bill->remaining_to_request; // accessor
        });

        // Paid this month (payment requests status paid, paid_at in current month)
        $paidThisMonth = PaymentRequest::query()
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        // Hutang Vendor yang belum dibuat bill (SEMUA kategori, semua status kecuali cancelled)
        $pendingVendorLegs = ShipmentLeg::query()
            // ->whereIn('cost_category', ['vendor', 'pelayaran']) // REMOVED: Semua kategori masuk hutang
            ->where('status', '!=', 'cancelled')
            ->whereHas('vendor')
            ->whereDoesntHave('vendorBillItems')
            ->with(['vendor', 'jobOrder', 'mainCost'])
            ->orderBy('load_date', 'asc')
            ->get();

        // Summary hutang vendor belum dibuat bill
        $totalPendingVendorLegs = $pendingVendorLegs->sum(function ($leg) {
            return $leg->mainCost ? $leg->mainCost->total : 0;
        });

        // Vendor Bills yang belum lunas
        $unpaidVendorBills = VendorBill::query()
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->with(['vendor', 'items', 'payments', 'paymentRequests'])
            ->orderBy('bill_date', 'asc')
            ->get();

        // Calculate DPP, PPN, PPH for each bill
        $unpaidVendorBills->transform(function ($bill) {
            $dpp = 0;
            $ppn = 0;
            $pph = 0;

            foreach ($bill->items as $item) {
                $desc = strtolower($item->description);
                if (str_contains($desc, 'ppn')) {
                    $ppn += abs($item->subtotal);
                } elseif (str_contains($desc, 'pph') || str_contains($desc, 'pph23')) {
                    $pph += abs($item->subtotal);
                } else {
                    if (! str_contains($desc, 'ppn') && ! str_contains($desc, 'pph')) {
                        $dpp += $item->subtotal;
                    }
                }
            }

            $bill->dpp = $dpp;
            $bill->ppn = $ppn;
            $bill->pph = $pph;
            $bill->total_paid = $bill->payments->sum('amount');
            $bill->remaining = $bill->total_amount - $bill->total_paid;
            $bill->has_active_payment_request = $bill->paymentRequests->contains(function ($request) {
                return in_array($request->status, ['pending', 'approved'], true);
            });

            return $bill;
        });

        $totalUnpaidVendorBills = $unpaidVendorBills->sum('total_amount');

        // Driver Advances yang pending
        $pendingDriverAdvances = DriverAdvance::query()
            ->where('status', 'pending')
            ->with(['driver', 'shipmentLeg.jobOrder'])
            ->orderBy('advance_date', 'asc')
            ->get();

        $totalPendingDriverAdvances = $pendingDriverAdvances->sum('amount');

        // Summary per vendor (SEMUA kategori: trucking, vendor, pelayaran, asuransi, pic)
        $vendorSummary = ShipmentLeg::query()
            ->select('shipment_legs.vendor_id', DB::raw('COUNT(*) as leg_count'), DB::raw('SUM(
                COALESCE(leg_main_costs.vendor_cost, 0) + 
                COALESCE(leg_main_costs.freight_cost, 0) + 
                COALESCE(leg_main_costs.uang_jalan, 0) + 
                COALESCE(leg_main_costs.bbm, 0) + 
                COALESCE(leg_main_costs.toll, 0) + 
                COALESCE(leg_main_costs.other_costs, 0) + 
                COALESCE(leg_main_costs.premium_cost, 0) + 
                COALESCE(leg_main_costs.pic_amount, 0) + 
                COALESCE(leg_main_costs.ppn, 0) - 
                COALESCE(leg_main_costs.pph23, 0)
            ) as total_vendor_cost'))
            ->leftJoin('leg_main_costs', 'leg_main_costs.shipment_leg_id', '=', 'shipment_legs.id')
            ->where('shipment_legs.status', '!=', 'cancelled')
            ->whereNotNull('shipment_legs.vendor_id')
            ->whereDoesntHave('vendorBillItems')
            ->groupBy('shipment_legs.vendor_id')
            ->with('vendor')
            ->orderByDesc('total_vendor_cost')
            ->get();

        // Summary per driver
        $driverSummary = DriverAdvance::query()
            ->select('driver_id', DB::raw('COUNT(*) as advance_count'), DB::raw('SUM(amount) as total_amount'))
            ->where('status', 'pending')
            ->groupBy('driver_id')
            ->with('driver')
            ->orderByDesc('total_amount')
            ->get();

        return view('hutang.dashboard', compact(
            'totalVendorBills',
            'totalRequested',
            'totalRemainingToRequest',
            'paidThisMonth',
            'pendingVendorLegs',
            'totalPendingVendorLegs',
            'unpaidVendorBills',
            'totalUnpaidVendorBills',
            'pendingDriverAdvances',
            'totalPendingDriverAdvances',
            'vendorSummary',
            'driverSummary'
        ));
    }
}
