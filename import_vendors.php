<?php

/**
 * Script untuk import data vendor dari Mastervendor.txt
 * Jalankan dengan: php import_vendors.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Master\Vendor;
use Illuminate\Support\Facades\DB;

$filePath = __DIR__ . '/Mastervendor.txt';

if (!file_exists($filePath)) {
    echo "❌ File Mastervendor.txt tidak ditemukan!\n";
    exit(1);
}

echo "📂 Membaca file Mastervendor.txt...\n";
$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$imported = 0;
$skipped = 0;
$updated = 0;
$errors = [];

DB::beginTransaction();

try {
    echo "🚀 Memulai import...\n\n";

    foreach ($lines as $lineNumber => $line) {
        // Parse data - format: nomor TAB nama_panjang TAB nama_pendek TAB kolom_kosong TAB ... TAB alamat
        $columns = array_map('trim', explode("\t", $line));

        if (count($columns) < 2) {
            $skipped++;
            continue;
        }

        $number = $columns[0];
        $fullName = $columns[1];
        $shortName = isset($columns[2]) ? $columns[2] : '';

        // Ambil kolom terakhir yang tidak kosong sebagai alamat
        $address = '';
        for ($i = count($columns) - 1; $i >= 2; $i--) {
            $value = $columns[$i];
            if (!empty($value) && $value !== '-') {
                $address = $value;
                break;
            }
        }

        // Skip jika nama kosong
        if (empty($fullName)) {
            $skipped++;
            echo "⏭️  Baris " . ($lineNumber + 1) . " dilewati (nama kosong)\n";
            continue;
        }

        // Gunakan nama lengkap sebagai nama utama
        $vendorName = $fullName;

        // Cek apakah vendor sudah ada
        $existingVendor = Vendor::where('name', $vendorName)->first();

        if ($existingVendor) {
            // Update alamat jika sebelumnya kosong dan sekarang ada alamat
            if (empty($existingVendor->address) && !empty($address)) {
                $existingVendor->update(['address' => $address]);
                $updated++;
                echo "🔄 Updated: {$vendorName} (alamat ditambahkan)\n";
            } else {
                $skipped++;
                echo "⏭️  Sudah ada: {$vendorName}\n";
            }
            continue;
        }

        // Extract email dan phone jika ada di data
        $phone = null;
        $email = null;
        $picName = null;

        // Cek kolom 3 dan 4 untuk contact info
        if (isset($columns[3]) && !empty($columns[3])) {
            if (filter_var($columns[3], FILTER_VALIDATE_EMAIL)) {
                $email = $columns[3];
            } elseif (preg_match('/^[\d\+\-\s\(\)]+$/', $columns[3])) {
                $phone = $columns[3];
            } else {
                $picName = $columns[3];
            }
        }

        if (isset($columns[4]) && !empty($columns[4])) {
            if (filter_var($columns[4], FILTER_VALIDATE_EMAIL)) {
                $email = $columns[4];
            } elseif (preg_match('/^[\d\+\-\s\(\)]+$/', $columns[4])) {
                $phone = $columns[4];
            }
        }

        // Create vendor
        Vendor::create([
            'name' => $vendorName,
            'address' => $address ?: null,
            'phone' => $phone,
            'email' => $email,
            'vendor_type' => 'subcon',
            'pic_name' => $picName,
            'pic_phone' => null,
            'pic_email' => null,
            'is_active' => true,
        ]);

        $imported++;
        echo "✅ Imported: {$vendorName}\n";
    }

    DB::commit();

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "📊 RINGKASAN IMPORT\n";
    echo str_repeat("=", 50) . "\n";
    echo "✅ Total diimport    : {$imported}\n";
    echo "🔄 Total diupdate    : {$updated}\n";
    echo "⏭️  Total dilewati    : {$skipped}\n";
    echo "📝 Total baris       : " . count($lines) . "\n";
    echo str_repeat("=", 50) . "\n";

    if (!empty($errors)) {
        echo "\n❌ ERRORS:\n";
        foreach ($errors as $error) {
            echo "  - {$error}\n";
        }
    }

    echo "\n✨ Import selesai!\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error during import: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
