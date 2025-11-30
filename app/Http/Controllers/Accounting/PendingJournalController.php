<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Finance\Invoice;
use App\Models\Finance\VendorBill;
use App\Models\Operations\DriverAdvance;
use Illuminate\Http\Request;

class PendingJournalController extends Controller
{
    /**
     * Get pending invoices that need to be posted to journal
     * Limit to 20 most recent for performance
     */
    public function getPendingInvoices()
    {
        return Invoice::unposted()
            ->with(['customer:id,name'])
            ->select('id', 'invoice_number', 'invoice_date', 'customer_id', 'total_amount', 'status')
            ->orderBy('invoice_date', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Get pending vendor bills that need to be posted to journal
     * Limit to 20 most recent for performance
     */
    public function getPendingVendorBills()
    {
        // Include amount_paid to avoid MissingAttributeException ketika
        // mengakses accessor outstanding_balance (butuh total_amount & amount_paid).
        return VendorBill::unposted()
            ->with(['vendor:id,name'])
            ->select('id', 'vendor_bill_number', 'bill_date', 'vendor_id', 'total_amount', 'amount_paid', 'status')
            ->orderBy('bill_date', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Get pending driver advances that need to be posted to journal
     * Limit to 20 most recent for performance
     */
    public function getPendingDriverAdvances()
    {
        return DriverAdvance::where('journal_status', 'unposted')
            ->with([
                'driver:id,name',
                'shipmentLeg:id,job_order_id',
                'shipmentLeg.jobOrder:id,job_number'
            ])
            ->select('id', 'advance_number', 'advance_date', 'driver_id', 'shipment_leg_id', 'journal_status')
            ->orderBy('advance_date', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Get summary counts for all pending journals
     * Fast count queries without loading full records
     */
    public function getSummary()
    {
        return [
            'invoices' => Invoice::unposted()->count(),
            'vendor_bills' => VendorBill::unposted()->count(),
            'driver_advances' => DriverAdvance::where('journal_status', 'unposted')->count(),
        ];
    }
}
