<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Simulate HTTP request
$request = Illuminate\Http\Request::create('/invoices/create', 'GET', [
    'customer_id' => 1,
    'status_filter' => 'all'
]);

$app->instance('request', $request);

// Get controller
$controller = new App\Http\Controllers\InvoiceController();

try {
    $response = $controller->create($request);
    
    $data = $response->getData();
    
    echo "=== Controller Response Data ===\n\n";
    echo "Customers count: " . $data['customers']->count() . "\n";
    echo "Job Orders count: " . $data['jobOrders']->count() . "\n";
    echo "Preview Items count: " . count($data['previewItems']) . "\n\n";
    
    if ($data['jobOrders']->count() > 0) {
        echo "Job Orders:\n";
        foreach ($data['jobOrders'] as $jo) {
            echo "  - {$jo->job_number} (ID: {$jo->id}, Status: {$jo->status})\n";
        }
    } else {
        echo "No job orders found!\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
