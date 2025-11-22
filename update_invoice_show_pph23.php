<?php

use App\Models\Finance\Invoice;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Update invoice ID 9 untuk menampilkan PPh 23
$invoice = Invoice::find(9);

if (!$invoice) {
    echo "Invoice tidak ditemukan.\n";
    exit;
}

echo "Invoice sebelum update:\n";
echo "- invoice_number: " . $invoice->invoice_number . "\n";
echo "- subtotal: " . number_format($invoice->subtotal, 2) . "\n";
echo "- pph23_amount: " . number_format($invoice->pph23_amount, 2) . "\n";
echo "- show_pph23: " . ($invoice->show_pph23 ? 'true' : 'false') . "\n\n";

// Hitung PPh 23 = 2% dari subtotal
$pph23 = $invoice->subtotal * 0.02;

$invoice->pph23_amount = $pph23;
$invoice->show_pph23 = true;
$invoice->save();

echo "Invoice setelah update:\n";
echo "- invoice_number: " . $invoice->invoice_number . "\n";
echo "- subtotal: " . number_format($invoice->subtotal, 2) . "\n";
echo "- pph23_amount: " . number_format($invoice->pph23_amount, 2) . " (2% dari subtotal)\n";
echo "- show_pph23: " . ($invoice->show_pph23 ? 'true' : 'false') . "\n\n";

echo "✅ Invoice berhasil diupdate!\n";
echo "Silakan buka: http://localhost:8000/invoices/{$invoice->id}/pdf\n";
