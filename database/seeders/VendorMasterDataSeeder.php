<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Master\Vendor;
use Illuminate\Support\Facades\DB;

class VendorMasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('Mastervendor.txt');

        if (!file_exists($filePath)) {
            $this->command->error("File Mastervendor.txt tidak ditemukan!");
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($lines as $lineNumber => $line) {
                // Parse data - format: nomor TAB nama_panjang TAB nama_pendek TAB ... TAB alamat
                $columns = explode("\t", $line);

                if (count($columns) < 2) {
                    $skipped++;
                    continue;
                }

                $number = trim($columns[0]);
                $fullName = trim($columns[1]);
                $shortName = isset($columns[2]) ? trim($columns[2]) : '';

                // Ambil kolom terakhir yang berisi sebagai alamat
                $address = '';
                for ($i = 2; $i < count($columns); $i++) {
                    $value = trim($columns[$i]);
                    if (!empty($value) && $value !== '-') {
                        $address = $value;
                    }
                }

                // Skip jika nama kosong
                if (empty($fullName)) {
                    $skipped++;
                    continue;
                }

                // Gunakan nama lengkap sebagai nama utama
                $vendorName = $fullName;

                // Cek apakah vendor sudah ada
                $existingVendor = Vendor::where('name', $vendorName)->first();

                if ($existingVendor) {
                    $skipped++;
                    $this->command->warn("Vendor sudah ada: {$vendorName}");
                    continue;
                }

                // Create vendor
                Vendor::create([
                    'name' => $vendorName,
                    'address' => $address ?: null,
                    'phone' => null,
                    'email' => null,
                    'vendor_type' => 'subcon', // default type
                    'pic_name' => null,
                    'pic_phone' => null,
                    'pic_email' => null,
                    'is_active' => true,
                ]);

                $imported++;
                $this->command->info("Imported: {$vendorName}");
            }

            DB::commit();

            $this->command->info("\n=== Import Summary ===");
            $this->command->info("Total imported: {$imported}");
            $this->command->info("Total skipped: {$skipped}");

            if (!empty($errors)) {
                $this->command->error("\n=== Errors ===");
                foreach ($errors as $error) {
                    $this->command->error($error);
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error during import: " . $e->getMessage());
            throw $e;
        }
    }
}
