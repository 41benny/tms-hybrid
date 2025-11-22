<?php
/**
 * Hapus semua vendor yang tidak punya transaksi apapun
 * Jalankan: php purge_non_transactional_vendors.php [--dry]
 *
 * Transaksi yang dianggap:
 * - vendor_bills, vendor_bill_items
 * - payment_requests
 * - cash_bank_transactions
 * - transports
 * - shipment_legs
 * - part_purchases
 * - leg_main_costs, leg_additional_costs
 * - trucks (kepemilikan armada dianggap aktivitas)
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Master\Vendor;

$dryRun = in_array('--dry', $argv, true);

$tables = [
    'vendor_bills', 'vendor_bill_items', 'payment_requests', 'cash_bank_transactions',
    'transports', 'shipment_legs', 'part_purchases', 'leg_main_costs', 'leg_additional_costs', 'trucks'
];

$protectedIds = collect();
$existingTables = [];
foreach ($tables as $t) {
    if (Schema::hasTable($t) && Schema::hasColumn($t, 'vendor_id')) {
        $existingTables[] = $t;
        $ids = DB::table($t)->whereNotNull('vendor_id')->pluck('vendor_id');
        $protectedIds = $protectedIds->merge($ids);
    }
}
$protectedIds = $protectedIds->unique()->values();

$totalVendors = Vendor::count();
$protectedCount = $protectedIds->count();

$toDelete = Vendor::whereNotIn('id', $protectedIds)->pluck('id');
$deleteCount = $toDelete->count();

echo "=== PURGE NON-TRANSACTIONAL VENDORS ===\n";
echo "Dry run        : " . ($dryRun ? 'YES' : 'NO') . "\n";
echo "Total vendors  : {$totalVendors}\n";
echo "Tables checked : " . implode(', ', $existingTables) . "\n";
echo "Protected IDs  : {$protectedCount}\n";
echo "Will delete    : {$deleteCount}\n";

// Sample names to keep and delete
$sampleKeep = Vendor::whereIn('id', $protectedIds->take(5))->pluck('name');
$sampleDelete = Vendor::whereIn('id', $toDelete->take(5))->pluck('name');

echo "Sample keep (max 5):\n";
foreach ($sampleKeep as $n) echo "  - {$n}\n";

echo "Sample delete (max 5):\n";
foreach ($sampleDelete as $n) echo "  - {$n}\n";

if ($dryRun) {
    echo "\nDry run selesai. Tidak ada data dihapus. Gunakan tanpa --dry untuk eksekusi.\n";
    exit(0);
}

if ($deleteCount === 0) {
    echo "\nTidak ada vendor yang akan dihapus. Selesai.\n";
    exit(0);
}

// Eksekusi penghapusan dengan chunk untuk efisiensi
Vendor::whereNotIn('id', $protectedIds)->chunkById(500, function ($vendors) {
    $ids = $vendors->pluck('id');
    Vendor::whereIn('id', $ids)->delete();
});

$remaining = Vendor::count();

echo "\n=== RESULT ===\n";
echo "Deleted vendors : {$deleteCount}\n";
echo "Remaining       : {$remaining}\n";

echo "\nPurge selesai.\n";
