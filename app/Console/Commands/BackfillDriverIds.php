<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Accounting\Journal;
use App\Models\Operations\DriverAdvance;
use Illuminate\Support\Facades\DB;

class BackfillDriverIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:backfill-driver-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill driver_id for existing journals linked to Driver Advances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting backfill of driver_id in journal_lines...');

        // 1. Backfill from Driver Advance (Initial Liability)
        $journals = Journal::where('source_type', 'driver_advance')->get();
        $bar = $this->output->createProgressBar(count($journals));
        $count = 0;

        foreach ($journals as $journal) {
            $advanceId = $journal->source_id;
            $advance = DriverAdvance::find($advanceId);

            if ($advance && $advance->driver_id) {
                $affected = DB::table('journal_lines')
                    ->where('journal_id', $journal->id)
                    ->whereNull('driver_id')
                    ->update(['driver_id' => $advance->driver_id]);
                    
                if ($affected > 0) $count += $affected;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info("Updated {$count} lines from 'driver_advance' source.");

        // 2. Backfill from Settlement (Uang Jalan) - THIS IS WHERE SAVINGS ARE
        $this->info('Starting backfill for Settlement (uang_jalan)...');
        
        $journals = Journal::where('source_type', 'uang_jalan')->get();
        $bar = $this->output->createProgressBar(count($journals));
        $countSettlement = 0;

        foreach ($journals as $journal) {
            // source_id is CashBankTransaction ID
            // Find linked DriverAdvancePayment to get the Driver
            $payment = \App\Models\Operations\DriverAdvancePayment::where('cash_bank_transaction_id', $journal->source_id)
                ->with('driverAdvance')
                ->first();

            if ($payment && $payment->driverAdvance && $payment->driverAdvance->driver_id) {
                $driverId = $payment->driverAdvance->driver_id;
                
                // Update lines with relevant accounts (Savings/Guarantee/Payable)
                // Or just update all lines in this journal? 
                // Updating all lines is safer for context, but specifically we need 2160/2170/2155
                $affected = DB::table('journal_lines')
                    ->where('journal_id', $journal->id)
                    ->whereNull('driver_id')
                    ->update(['driver_id' => $driverId]);
                    
                if ($affected > 0) $countSettlement += $affected;
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Updated {$countSettlement} lines from 'uang_jalan' source.");

        // 2. Backfill from CashBankTransactions (Withdrawals)
        // If there were any old "manual" transactions that we can identify?
        // Maybe hard to identify without specific markers.
        // We'll skip for now to avoid false positives. 
        // Existing data likely only has reliable driver links via DriverAdvance.

        $this->info('Backfill completed.');
    }
}
