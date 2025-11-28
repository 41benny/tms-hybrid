<?php

use App\Models\Finance\Invoice;

$invoices = Invoice::where('status', 'paid')
    ->whereDate('updated_at', today())
    ->with('customer')
    ->get();

foreach ($invoices as $invoice) {
    echo $invoice->invoice_number . " | " . ($invoice->customer->name ?? 'No Customer') . " | " . $invoice->updated_at . "\n";
}
