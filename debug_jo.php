<?php

use App\Models\Operations\JobOrder;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jo = JobOrder::where('job_number', 'JOB-251119-001')->first();

if (!$jo) {
    echo "Job Order not found\n";
    exit;
}

echo "JO ID: " . $jo->id . "\n";
echo "JO Invoice Amount: " . $jo->invoice_amount . "\n";
echo "JO Total Billable: " . $jo->total_billable . "\n";
echo "JO Status: " . $jo->status . "\n";

echo "\n--- Invoice Items Linked to JO ---\n";
foreach ($jo->invoiceItems as $item) {
    echo "Item ID: " . $item->id . " | Invoice ID: " . $item->invoice_id . " | Desc: " . $item->description . " | Amount: " . $item->amount . "\n";
}

echo "\n--- Invoices Linked to JO ---\n";
$invs = $jo->invoices()->with('items')->get();
foreach ($invs as $inv) {
    echo "Invoice: " . $inv->invoice_number . " | Status: " . $inv->status . " | Total: " . $inv->total_amount . "\n";
    foreach ($inv->items as $item) {
        echo "  - Item: " . $item->description . " | Qty: " . $item->quantity . " | Price: " . $item->unit_price . " | Amount: " . $item->amount . " | JO ID: " . $item->job_order_id . "\n";
    }
}
