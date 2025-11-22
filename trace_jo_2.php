<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$jo = App\Models\Operations\JobOrder::with('shipmentLegs.additionalCosts', 'shipmentLegs.mainCost')
    ->find(2); // JOB-251111-002

if (!$jo) {
    echo "Job Order not found!\n";
    exit;
}

echo "=== JOB ORDER DEBUG ===\n\n";
echo "Job Number: {$jo->job_number}\n";
echo "ID: {$jo->id}\n";
echo "Status: {$jo->status}\n";
echo "Invoice Amount: {$jo->invoice_amount}\n";
echo "Total Billable: {$jo->total_billable}\n\n";

echo "=== INVOICE ITEMS ===\n";
$items = $jo->invoiceItems()->get();
echo "Total Items: " . $items->count() . "\n";
foreach ($items as $item) {
    echo "  - {$item->description}: {$item->subtotal}\n";
}

$totalInvoiced = $jo->invoiceItems()->sum('subtotal');
echo "\nTotal Invoiced (sum of subtotal): {$totalInvoiced}\n";
echo "Expected Amount (invoice_amount + total_billable): " . ($jo->invoice_amount + $jo->total_billable) . "\n\n";

echo "isFullyInvoiced() result: " . ($jo->isFullyInvoiced() ? 'YES' : 'NO') . "\n";
echo "Should appear in list: " . (!$jo->isFullyInvoiced() ? 'YES' : 'NO') . "\n";
