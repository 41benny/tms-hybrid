<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Finance\Invoice;
use App\Models\Accounting\TaxInvoiceRequest;
use App\Models\Operations\PaymentRequest;

class CleanOrphanedNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:clean-orphaned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove notifications that reference deleted data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Scanning for orphaned notifications...');
        
        $deletedCount = 0;
        $notifications = DB::table('notifications')->get();
        
        foreach ($notifications as $notification) {
            $data = json_decode($notification->data, true);
            $shouldDelete = false;
            
            // Check if the referenced data still exists
            switch ($notification->type) {
                case 'App\\Notifications\\InvoiceSubmittedForApproval':
                    if (!Invoice::where('invoice_number', $data['invoice_number'] ?? '')->exists()) {
                        $shouldDelete = true;
                        $this->warn("  ✗ Invoice notification orphaned: {$data['invoice_number']}");
                    }
                    break;
                    
                case 'App\\Notifications\\TaxInvoiceRequestedNotification':
                    if (!TaxInvoiceRequest::where('request_number', $data['request_number'] ?? '')->exists()) {
                        $shouldDelete = true;
                        $this->warn("  ✗ Tax invoice request notification orphaned: {$data['request_number']}");
                    }
                    break;
                    
                case 'App\\Notifications\\PaymentRequestCreated':
                    if (!PaymentRequest::where('request_number', $data['request_number'] ?? '')->exists()) {
                        $shouldDelete = true;
                        $this->warn("  ✗ Payment request notification orphaned: {$data['request_number']}");
                    }
                    break;
            }
            
            if ($shouldDelete) {
                DB::table('notifications')->where('id', $notification->id)->delete();
                $deletedCount++;
            }
        }
        
        if ($deletedCount > 0) {
            $this->info("\n✅ Cleaned up {$deletedCount} orphaned notification(s).");
        } else {
            $this->info("\n✅ No orphaned notifications found.");
        }
        
        return 0;
    }
}
