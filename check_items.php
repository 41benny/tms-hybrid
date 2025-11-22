<?php

use App\Models\Finance\InvoiceItem;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = InvoiceItem::latest()->take(5)->get();
foreach ($items as $item) {
    echo "ID: {$item->id}, InvID: {$item->invoice_id}, Qty: {$item->quantity}, Amount: {$item->amount}\n";
}
