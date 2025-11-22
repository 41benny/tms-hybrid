<?php

use App\Models\Finance\Invoice;
use App\Models\Accounting\Journal;
use App\Services\Accounting\JournalService;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Mulai pengecekan invoice yang kehilangan jurnal...\n";

$invoices = Invoice::where('status', 'sent')->get();
$count = 0;
$fixed = 0;

$service = app(JournalService::class);

foreach ($invoices as $invoice) {
    $exists = Journal::where('source_type', 'invoice')
        ->where('source_id', $invoice->id)
        ->exists();

    if (!$exists) {
        echo "Invoice {$invoice->invoice_number} (ID: {$invoice->id}) status SENT tapi tidak ada jurnal. Memperbaiki...\n";
        
        try {
            DB::beginTransaction();
            $journal = $service->postInvoice($invoice);
            DB::commit();
            echo "  -> Berhasil! Jurnal ID: {$journal->id}\n";
            $fixed++;
        } catch (\Exception $e) {
            DB::rollBack();
            echo "  -> Gagal: " . $e->getMessage() . "\n";
        }
    } else {
        // echo "Invoice {$invoice->invoice_number} aman.\n";
    }
    $count++;
}

echo "\nSelesai. Total Invoice Sent: $count. Diperbaiki: $fixed.\n";
