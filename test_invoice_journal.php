<?php

use App\Models\Finance\Invoice;
use App\Services\Accounting\JournalService;
use Illuminate\Support\Facades\Log;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Ambil invoice terakhir
$invoice = Invoice::latest()->first();

if (!$invoice) {
    echo "Tidak ada invoice ditemukan.\n";
    exit;
}

echo "Testing JournalService untuk Invoice ID: " . $invoice->id . " (" . $invoice->invoice_number . ")\n";

if (class_exists(JournalService::class)) {
    echo "Class JournalService ditemukan.\n";
    try {
        $service = app(JournalService::class);
        echo "Service berhasil di-instantiate.\n";
        
        // Cek apakah sudah ada jurnal
        $existing = \App\Models\Accounting\Journal::where('source_type', 'invoice')->where('source_id', $invoice->id)->first();
        if ($existing) {
            echo "Jurnal sudah ada untuk invoice ini. ID: " . $existing->id . "\n";
        } else {
            echo "Belum ada jurnal. Mencoba membuat...\n";
            $journal = $service->postInvoice($invoice);
            echo "Jurnal berhasil dibuat! ID: " . $journal->id . "\n";
        }
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
    }
} else {
    echo "Class JournalService TIDAK ditemukan via class_exists.\n";
}
