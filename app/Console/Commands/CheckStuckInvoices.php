<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckStuckInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:check-stuck-invoices {--fix : Fix the invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and fix invoices that are paid but have no active payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoices = \App\Models\Finance\Invoice::where('status', 'paid')
            ->whereDate('updated_at', today())
            ->get();

        $this->info("Found " . $invoices->count() . " paid invoices updated today.");

        foreach ($invoices as $invoice) {
            // Check if it has any active payments linked via pivot or new table
            $pivotPayments = \DB::table('invoice_payments')->where('invoice_id', $invoice->id)->sum('allocated_amount');
            $trxPayments = \App\Models\Finance\InvoiceTransactionPayment::where('invoice_id', $invoice->id)->sum('amount_paid');
            
            // Also check legacy single transaction link
            $legacyPayment = \App\Models\Finance\CashBankTransaction::where('invoice_id', $invoice->id)
                ->whereNull('voided_at')
                ->sum('amount');

            $totalPaid = $pivotPayments + $trxPayments + $legacyPayment;

            $this->line("Invoice {$invoice->invoice_number} ({$invoice->customer->name}): Total Amount: {$invoice->total_amount}, Paid Amount: {$invoice->paid_amount}, Calculated Payment: {$totalPaid}");

            if ($totalPaid < $invoice->total_amount) {
                $this->error("  -> MISMATCH! This invoice seems stuck.");
                
                if ($this->option('fix')) {
                    $invoice->update([
                        'status' => 'sent',
                        'paid_amount' => $totalPaid,
                        'paid_at' => null
                    ]);
                    $this->info("  -> FIXED: Status reset to sent, paid_amount to {$totalPaid}");
                }
            }
        }
    }
}
