<?php

namespace App\Console\Commands;

use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalLine;
use App\Models\Finance\CashBankTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateJournalDescriptions extends Command
{
    protected $signature = 'journal:update-descriptions {--dry-run : Show what would be updated without actually updating}';
    protected $description = 'Update existing journal descriptions to include customer/vendor/driver names';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting journal description update...');
        
        $updatedCount = 0;
        
        // 1. Update Customer Payment journals
        $updatedCount += $this->updateCustomerPaymentJournals($isDryRun);
        
        // 2. Update Vendor Payment journals
        $updatedCount += $this->updateVendorPaymentJournals($isDryRun);
        
        // 3. Update Driver Advance Payment journals
        $updatedCount += $this->updateDriverAdvancePaymentJournals($isDryRun);
        
        $this->newLine();
        $this->info("✅ Total updated: {$updatedCount} journal lines");
        
        if ($isDryRun) {
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }
        
        return 0;
    }

    protected function updateCustomerPaymentJournals(bool $isDryRun): int
    {
        $this->info('📝 Processing Customer Payment journals...');
        
        $journals = Journal::where('source_type', 'customer_payment')
            ->with(['lines'])
            ->get();
        
        $updatedCount = 0;
        
        foreach ($journals as $journal) {
            $trx = CashBankTransaction::with(['customer', 'invoice'])->find($journal->source_id);
            if (!$trx || !$trx->customer) {
                continue;
            }
            
            $customerName = $trx->customer->name;
            $invoiceNumber = $trx->invoice?->invoice_number ?? '';
            
            foreach ($journal->lines as $line) {
                $desc = $line->description;
                $newDesc = null;
                
                // Skip if already has customer name
                if (str_contains($desc, ' - ' . $customerName)) {
                    continue;
                }
                
                // Update descriptions based on pattern
                if (str_starts_with($desc, 'Penerimaan invoice') && $invoiceNumber && !str_contains($desc, ' - ')) {
                    $newDesc = $desc . ' - ' . $customerName;
                } elseif (str_starts_with($desc, 'Piutang PPh 23 invoice') && $invoiceNumber && !str_contains($desc, ' - ')) {
                    $newDesc = $desc . ' - ' . $customerName;
                } elseif (str_starts_with($desc, 'Pelunasan invoice') && $invoiceNumber && !str_contains($desc, ' - ')) {
                    $newDesc = $desc . ' - ' . $customerName;
                }
                
                if ($newDesc) {
                    if ($isDryRun) {
                        $this->line("  Would update: '{$desc}' → '{$newDesc}'");
                    } else {
                        $line->update(['description' => $newDesc]);
                    }
                    $updatedCount++;
                }
            }
            
            // Update memo
            if (!str_contains($journal->memo, ' - ')) {
                $newMemo = $journal->memo . ' - ' . $customerName;
                if ($isDryRun) {
                    $this->line("  Would update memo: '{$journal->memo}' → '{$newMemo}'");
                } else {
                    $journal->update(['memo' => $newMemo]);
                }
            }
        }
        
        $this->info("  ✓ Customer Payment: {$updatedCount} lines processed");
        return $updatedCount;
    }

    protected function updateVendorPaymentJournals(bool $isDryRun): int
    {
        $this->info('📝 Processing Vendor Payment journals...');
        
        $journals = Journal::where('source_type', 'vendor_payment')
            ->with(['lines'])
            ->get();
        
        $updatedCount = 0;
        
        foreach ($journals as $journal) {
            $trx = CashBankTransaction::with('vendor')->find($journal->source_id);
            if (!$trx || !$trx->vendor) {
                continue;
            }
            
            $vendorName = $trx->vendor->name;
            
            foreach ($journal->lines as $line) {
                $desc = $line->description;
                $newDesc = null;
                
                // Skip if already has vendor name
                if (str_contains($desc, ' - ' . $vendorName)) {
                    continue;
                }
                
                // Update descriptions based on pattern
                if ($desc === 'Pelunasan hutang vendor') {
                    $newDesc = $desc . ' - ' . $vendorName;
                } elseif ($desc === 'Pembayaran hutang vendor') {
                    $newDesc = $desc . ' - ' . $vendorName;
                }
                
                if ($newDesc) {
                    if ($isDryRun) {
                        $this->line("  Would update: '{$desc}' → '{$newDesc}'");
                    } else {
                        $line->update(['description' => $newDesc]);
                    }
                    $updatedCount++;
                }
            }
            
            // Update memo
            if ($journal->memo === 'Pembayaran vendor') {
                $newMemo = $journal->memo . ' - ' . $vendorName;
                if ($isDryRun) {
                    $this->line("  Would update memo: '{$journal->memo}' → '{$newMemo}'");
                } else {
                    $journal->update(['memo' => $newMemo]);
                }
            }
        }
        
        $this->info("  ✓ Vendor Payment: {$updatedCount} lines processed");
        return $updatedCount;
    }

    protected function updateDriverAdvancePaymentJournals(bool $isDryRun): int
    {
        $this->info('📝 Processing Driver Advance Payment journals...');
        
        $journals = Journal::where('source_type', 'uang_jalan')
            ->with(['lines'])
            ->get();
        
        $updatedCount = 0;
        
        foreach ($journals as $journal) {
            // Get driver name from DriverAdvancePayment
            $driverAdvancePayment = \App\Models\Operations\DriverAdvancePayment::where('cash_bank_transaction_id', $journal->source_id)
                ->with('driverAdvance.driver')
                ->first();
            
            if (!$driverAdvancePayment || !$driverAdvancePayment->driverAdvance || !$driverAdvancePayment->driverAdvance->driver) {
                continue;
            }
            
            $driverName = $driverAdvancePayment->driverAdvance->driver->name;
            
            foreach ($journal->lines as $line) {
                $desc = $line->description;
                $newDesc = null;
                
                // Skip if already has driver name (check if ends with driver name pattern)
                if (preg_match('/ - [A-Za-z\s]+$/', $desc) && str_contains($desc, $driverName)) {
                    continue;
                }
                
                // Update descriptions based on pattern
                if (preg_match('/^Pembayaran hutang uang jalan - (Pelunasan|DP)$/', $desc)) {
                    $newDesc = $desc . ' - ' . $driverName;
                } elseif ($desc === 'Pembayaran uang jalan driver') {
                    $newDesc = 'Pembayaran uang jalan - ' . $driverName;
                } elseif ($desc === 'Potongan tabungan supir') {
                    $newDesc = $desc . ' - ' . $driverName;
                } elseif ($desc === 'Potongan jaminan supir') {
                    $newDesc = $desc . ' - ' . $driverName;
                }
                
                if ($newDesc) {
                    if ($isDryRun) {
                        $this->line("  Would update: '{$desc}' → '{$newDesc}'");
                    } else {
                        $line->update(['description' => $newDesc]);
                    }
                    $updatedCount++;
                }
            }
        }
        
        $this->info("  ✓ Driver Advance Payment: {$updatedCount} lines processed");
        return $updatedCount;
    }
}
