<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Master\Vendor;

class NewVendorImportSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('Mastervendor.txt');
        if (!file_exists($path)) {
            $this->command->error('File Mastervendor.txt tidak ditemukan');
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $imported = 0; $skipped = 0; $updated = 0; $errors = [];

        // Ambil vendor yang sudah punya transaksi (hasil purge sebelumnya)
        $protectedVendorIds = Vendor::pluck('id')->toArray(); // kita tidak hapus apapun lagi di sini

        DB::beginTransaction();
        try {
            foreach ($lines as $lineNum => $raw) {
                // Normalisasi multiple tab menjadi satu delimiter \t
                $line = preg_replace('/\t+/', "\t", $raw);
                $cols = array_map('trim', explode("\t", $line));
                if (count($cols) === 0) { continue; }

                // Skip header row (check common header keywords)
                $firstCol = strtolower($cols[0] ?? '');
                if ($lineNum === 0 || in_array($firstCol, ['vendor name', 'name', 'no', 'nomor', '#'])) {
                    $skipped++;
                    continue;
                }

                // Tentukan apakah kolom pertama adalah nomor indeks
                $nameIndex = 0;
                if (is_numeric($cols[0]) && isset($cols[1])) {
                    $nameIndex = 1; // nama sebenarnya di kolom kedua
                }

                $fullName = $cols[$nameIndex] ?? null;
                if (!$fullName) { $skipped++; continue; }

                // Short name: kolom setelah fullName jika berbeda
                $shortName = null;
                $shortIdx = $nameIndex + 1;
                if (isset($cols[$shortIdx]) && $cols[$shortIdx] !== '' && !preg_match('/^\+?\d[\d\s\-]+$/', $cols[$shortIdx]) && !filter_var($cols[$shortIdx], FILTER_VALIDATE_EMAIL)) {
                    $shortName = $cols[$shortIdx];
                }

                // Cari email & phone di kolom-kolom
                $email = null; $phone = null; $picName = null; $address = null;
                foreach ($cols as $i => $c) {
                    if ($i <= $nameIndex) continue; // skip kolom nama / indeks
                    if ($c === '') continue;
                    if (!$email && filter_var($c, FILTER_VALIDATE_EMAIL)) { $email = $c; continue; }
                    if (!$phone && preg_match('/^(\+?\d?[\d\s\-\(\)]{6,})$/', $c)) { $phone = $c; continue; }
                    if (!$picName && $i <= $nameIndex + 3 && !str_contains(strtolower($c), 'jl') && strlen($c) <= 40 && !preg_match('/\d{5}/', $c)) {
                        // heuristik pic name (awalan kolom dekat nama, pendek, bukan alamat)
                        $picName = $c; continue;
                    }
                }

                // Heuristik alamat: ambil kolom terpanjang yang mengandung 'Jl' atau 'RT' atau spasi > 5
                $candidateAddress = null; $maxLen = 0;
                foreach ($cols as $c) {
                    $lc = strtolower($c);
                    if (strlen($c) > $maxLen && (str_contains($lc, 'jl') || str_contains($lc, 'rt') || str_contains($lc, 'rw') || strlen($c) > 25)) {
                        $candidateAddress = $c; $maxLen = strlen($c);
                    }
                }
                if ($candidateAddress) { $address = $candidateAddress; }

                // Cek existing
                $existing = Vendor::where('name', $fullName)->first();
                if ($existing) {
                    // Update alamat jika kosong tapi sekarang ada
                    $changed = false;
                    $updateData = [];
                    if (!$existing->address && $address) { $updateData['address'] = $address; $changed = true; }
                    if (!$existing->email && $email) { $updateData['email'] = $email; $changed = true; }
                    if (!$existing->phone && $phone) { $updateData['phone'] = $phone; $changed = true; }
                    if (!$existing->pic_name && $picName) { $updateData['pic_name'] = $picName; $changed = true; }
                    if ($changed) { $existing->update($updateData); $updated++; }
                    else { $skipped++; }
                    continue;
                }

                Vendor::create([
                    'name' => $fullName,
                    'address' => $address,
                    'phone' => $phone,
                    'email' => $email,
                    'vendor_type' => 'subcon',
                    'pic_name' => $picName,
                    'pic_phone' => null,
                    'pic_email' => null,
                    'is_active' => true,
                ]);
                $imported++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = $e->getMessage();
            $this->command->error('Gagal import: '.$e->getMessage());
        }

        $this->command->info("=== SUMMARY ===");
        $this->command->info('Imported : '.$imported);
        $this->command->info('Updated  : '.$updated);
        $this->command->info('Skipped  : '.$skipped);
        if ($errors) {
            foreach ($errors as $er) { $this->command->error($er); }
        }
    }
}
