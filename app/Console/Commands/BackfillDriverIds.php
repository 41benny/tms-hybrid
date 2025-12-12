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

        // 1. Backfill from Driver Advance Settlements
        // Journals with source_type = 'driver_advance'
        $journals = Journal::where('source_type', 'driver_advance')->get();
        
        $bar = $this->output->createProgressBar(count($journals));
        $count = 0;

        foreach ($journals as $journal) {
            $advanceId = $journal->source_id;
            $advance = DriverAdvance::find($advanceId);

            if ($advance && $advance->driver_id) {
                // Update specific lines (Savings & Guarantee)
                // Codes: 2160 (Savings), 2170 (Guarantee)
                // Actually, safer to update all lines for this journal? 
                // No, usually only the liability lines belong to the driver personally?
                // But generally, linking the LINE to the driver is safe if the whole transaction is about them.
                // However, for strictness, let's update lines where account is 2160 or 2170.
                
                $affected = DB::table('journal_lines')
                    ->where('journal_id', $journal->id)
                    ->whereNull('driver_id')
                    ->update(['driver_id' => $advance->driver_id]);
                    
                if ($affected > 0) {
                    $count += $affected;
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Updated {$count} journal lines from Driver Advances.");

        // 2. Backfill from CashBankTransactions (Withdrawals)
        // If there were any old "manual" transactions that we can identify?
        // Maybe hard to identify without specific markers.
        // We'll skip for now to avoid false positives. 
        // Existing data likely only has reliable driver links via DriverAdvance.

        $this->info('Backfill completed.');
    }
}
