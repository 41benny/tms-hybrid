<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Operations\DriverAdvance;
use App\Models\Operations\PaymentRequest;
use App\Models\Finance\VendorBill;

class RegenerateNotesCommand extends Command
{
    protected $signature = 'notes:regenerate 
                            {--model= : Specific model to regenerate (driver-advance, payment-request, vendor-bill, or all)}
                            {--force : Force update even if notes are not empty}';

    protected $description = 'Regenerate auto-description notes for Driver Advances, Payment Requests, and Vendor Bills';

    public function handle()
    {
        $model = $this->option('model') ?: 'all';
        $force = $this->option('force');

        if (in_array($model, ['driver-advance', 'all'])) {
            $this->regenerateDriverAdvances($force);
        }

        if (in_array($model, ['payment-request', 'all'])) {
            $this->regeneratePaymentRequests($force);
        }

        if (in_array($model, ['vendor-bill', 'all'])) {
            $this->regenerateVendorBills($force);
        }

        $this->info('✅ Regeneration complete!');
    }

    protected function regenerateDriverAdvances($force)
    {
        $this->info('🔄 Regenerating Driver Advance notes...');

        $query = DriverAdvance::with(['driver', 'shipmentLeg.truck', 'shipmentLeg.jobOrder.customer']);

        if (!$force) {
            // Only update empty or auto-generated notes
            $query->where(function($q) {
                $q->whereNull('notes')
                  ->orWhere('notes', '')
                  ->orWhere('notes', 'like', 'Auto-generated from Leg%');
            });
        }

        $advances = $query->get();
        $count = 0;

        foreach ($advances as $advance) {
            $newNotes = $advance->generateAutoDescription();
            if ($advance->notes !== $newNotes) {
                $advance->notes = $newNotes;
                $advance->save();
                $count++;
            }
        }

        $this->info("   Updated {$count} Driver Advance records");
    }

    protected function regeneratePaymentRequests($force)
    {
        $this->info('🔄 Regenerating Payment Request notes...');

        $query = PaymentRequest::with(['requestedBy', 'vendorBill', 'vendor']);

        if (!$force) {
            $query->where(function($q) {
                $q->whereNull('notes')
                  ->orWhere('notes', '');
            });
        }

        $requests = $query->get();
        $count = 0;

        foreach ($requests as $request) {
            $newNotes = $request->generateAutoDescription();
            if ($request->notes !== $newNotes) {
                $request->notes = $newNotes;
                $request->save();
                $count++;
            }
        }

        $this->info("   Updated {$count} Payment Request records");
    }

    protected function regenerateVendorBills($force)
    {
        $this->info('🔄 Regenerating Vendor Bill notes...');

        $query = VendorBill::with(['vendor', 'items']);

        if (!$force) {
            $query->where(function($q) {
                $q->whereNull('notes')
                  ->orWhere('notes', '');
            });
        }

        $bills = $query->get();
        $count = 0;

        foreach ($bills as $bill) {
            $newNotes = $bill->generateAutoDescription();
            if ($bill->notes !== $newNotes) {
                $bill->notes = $newNotes;
                $bill->save();
                $count++;
            }
        }

        $this->info("   Updated {$count} Vendor Bill records");
    }
}
