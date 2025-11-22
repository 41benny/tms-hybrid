<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Read the data customer.MD file
        $filePath = base_path('data customer.MD');

        if (!File::exists($filePath)) {
            $this->command->error('File data customer.MD not found!');
            return;
        }

        $content = File::get($filePath);
        $lines = explode("\n", $content);

        // Skip header line
        array_shift($lines);

        $imported = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines
            if (empty($line)) {
                continue;
            }

            // Split by tab
            $parts = explode("\t", $line);

            // Extract data
            $name = isset($parts[0]) && !empty(trim($parts[0])) ? trim($parts[0]) : null;
            $contactPerson = isset($parts[1]) && !empty(trim($parts[1])) ? trim($parts[1]) : null;
            $email = isset($parts[2]) && !empty(trim($parts[2])) ? trim($parts[2]) : null;
            $phone = isset($parts[3]) && !empty(trim($parts[3])) ? trim($parts[3]) : null;
            $address = isset($parts[4]) && !empty(trim($parts[4])) ? trim($parts[4]) : null;

            // Skip if name is empty
            if (empty($name)) {
                $skipped++;
                continue;
            }

            // Check if customer already exists
            $exists = DB::table('customers')->where('name', $name)->exists();

            if ($exists) {
                $this->command->warn("Customer already exists: {$name}");
                $skipped++;
                continue;
            }

            // Insert customer
            try {
                DB::table('customers')->insert([
                    'name' => $name,
                    'contact_person' => $contactPerson,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $imported++;
                $this->command->info("Imported: {$name}");
            } catch (\Exception $e) {
                $this->command->error("Failed to import {$name}: " . $e->getMessage());
                $skipped++;
            }
        }

        $this->command->info("\n=== Import Summary ===");
        $this->command->info("Imported: {$imported} customers");
        $this->command->info("Skipped: {$skipped} customers");
    }
}
