<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate request
$customerId = 1; // PT Sejahtera Abadi
$statusFilter = 'all';

echo "=== Testing Invoice Create Logic ===\n\n";

// Test 1: Get customers
$customers = App\Models\Master\Customer::whereHas('jobOrders', function($q) use ($statusFilter) {
    if ($statusFilter === 'completed') {
        $q->where('status', 'completed');
    }
})->orderBy('name')->get();

echo "Customers found: " . $customers->count() . "\n";
foreach ($customers as $c) {
    echo "  - {$c->name} (ID: {$c->id})\n";
}

echo "\n";

// Test 2: Get job orders for customer
if ($customerId) {
    $jobOrderQuery = App\Models\Operations\JobOrder::with(['shipmentLegs.additionalCosts', 'shipmentLegs.mainCost'])
        ->where('customer_id', $customerId);
    
    if ($statusFilter === 'completed') {
        $jobOrderQuery->where('status', 'completed');
    }
    
    $allJobOrders = $jobOrderQuery->orderBy('created_at', 'desc')->get();
    echo "Job Orders (before filter): " . $allJobOrders->count() . "\n";
    
    foreach ($allJobOrders as $jo) {
        echo "\n  JO: {$jo->job_number}\n";
        echo "    Status: {$jo->status}\n";
        echo "    Invoice Amount: {$jo->invoice_amount}\n";
        echo "    Total Billable: {$jo->total_billable}\n";
        echo "    Total Invoiced: {$jo->total_invoiced}\n";
        echo "    Fully Invoiced: " . ($jo->isFullyInvoiced() ? 'YES' : 'NO') . "\n";
    }
    
    $jobOrders = $allJobOrders->filter(fn($jo) => !$jo->isFullyInvoiced())->values();
    
    echo "\n\nJob Orders (after filter - not fully invoiced): " . $jobOrders->count() . "\n";
    foreach ($jobOrders as $jo) {
        echo "  - {$jo->job_number} (ID: {$jo->id})\n";
    }
}
