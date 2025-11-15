<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            // ====== ASET (Kepala 1) ======
            // Kas & Setara Kas
            ['code' => '1100', 'name' => 'Kas & Setara Kas', 'type' => 'asset', 'is_postable' => false],
            ['code' => '1110', 'name' => 'Kas Besar', 'type' => 'asset', 'is_cash' => true],
            ['code' => '1115', 'name' => 'Kas Kecil', 'type' => 'asset', 'is_cash' => true],
            ['code' => '1120', 'name' => 'Bank Operasional', 'type' => 'asset', 'is_bank' => true],
            ['code' => '1121', 'name' => 'Bank BCA', 'type' => 'asset', 'is_bank' => true],
            ['code' => '1122', 'name' => 'Bank Mandiri', 'type' => 'asset', 'is_bank' => true],
            ['code' => '1130', 'name' => 'Bank Pinjaman', 'type' => 'asset', 'is_bank' => true],

            // Piutang
            ['code' => '1200', 'name' => 'Piutang Usaha', 'type' => 'asset'],
            ['code' => '1300', 'name' => 'Piutang Lain-lain', 'type' => 'asset'],

            // Persediaan & Uang Muka
            ['code' => '1400', 'name' => 'Persediaan Sparepart', 'type' => 'asset'],
            ['code' => '1500', 'name' => 'Uang Muka & Biaya Dimuka', 'type' => 'asset'],
            ['code' => '1530', 'name' => 'Piutang PPh Dipotong (PPh 23)', 'type' => 'asset'],

            // Aset Tetap (opsional, bisa diperluas)
            ['code' => '1600', 'name' => 'Aset Tetap', 'type' => 'asset', 'is_postable' => false],
            ['code' => '1610', 'name' => 'Kendaraan', 'type' => 'asset'],
            ['code' => '1620', 'name' => 'Akumulasi Penyusutan Kendaraan', 'type' => 'asset'],

            // ====== KEWAJIBAN (Kepala 2) ======
            // Hutang Usaha
            ['code' => '2100', 'name' => 'Hutang Usaha', 'type' => 'liability'],

            // Hutang Pajak
            ['code' => '2200', 'name' => 'Hutang Pajak', 'type' => 'liability', 'is_postable' => false],
            ['code' => '2210', 'name' => 'PPN Keluaran', 'type' => 'liability'],
            ['code' => '2220', 'name' => 'PPN Masukan', 'type' => 'asset'],
            ['code' => '2230', 'name' => 'PPh 21', 'type' => 'liability'],
            ['code' => '2240', 'name' => 'PPh 23', 'type' => 'liability'],

            // Biaya Yang Masih Harus Dibayar (BYMH / Accrued Expenses)
            ['code' => '2300', 'name' => 'Biaya Yang Masih Harus Dibayar', 'type' => 'liability', 'is_postable' => false],
            ['code' => '2310', 'name' => 'BYMH - Gaji', 'type' => 'liability'],
            ['code' => '2320', 'name' => 'BYMH - Provisi Bank', 'type' => 'liability'],
            ['code' => '2330', 'name' => 'BYMH - Lainnya', 'type' => 'liability'],

            // Hutang Jangka Pendek
            ['code' => '2400', 'name' => 'Hutang Jangka Pendek', 'type' => 'liability', 'is_postable' => false],
            ['code' => '2410', 'name' => 'Pinjaman Bank A (JK Pendek)', 'type' => 'liability'],
            ['code' => '2420', 'name' => 'Pinjaman Bank B (JK Pendek)', 'type' => 'liability'],

            // Hutang Jangka Panjang
            ['code' => '2500', 'name' => 'Hutang Jangka Panjang', 'type' => 'liability', 'is_postable' => false],
            ['code' => '2510', 'name' => 'Pinjaman Bank A (JK Panjang)', 'type' => 'liability'],
            ['code' => '2520', 'name' => 'Pinjaman Bank B (JK Panjang)', 'type' => 'liability'],

            // Akun Sementara / Ayat Silang
            ['code' => '2900', 'name' => 'Akun Sementara (Suspense)', 'type' => 'liability'],

            // ====== EKUITAS (Kepala 3) ======
            ['code' => '3100', 'name' => 'Modal', 'type' => 'equity', 'is_postable' => false],
            ['code' => '3110', 'name' => 'Modal Disetor', 'type' => 'equity'],
            ['code' => '3200', 'name' => 'Laba Ditahan', 'type' => 'equity'],
            ['code' => '3300', 'name' => 'Prive', 'type' => 'equity'],

            // ====== PENDAPATAN USAHA (Kepala 4) ======
            ['code' => '4100', 'name' => 'Pendapatan Jasa Angkutan', 'type' => 'revenue'],
            ['code' => '4200', 'name' => 'Pendapatan Jasa Lainnya', 'type' => 'revenue'],

            // ====== HPP / BEBAN POKOK (Kepala 5) ======
            ['code' => '5100', 'name' => 'Beban BBM', 'type' => 'expense'],
            ['code' => '5110', 'name' => 'Beban Tol', 'type' => 'expense'],
            ['code' => '5120', 'name' => 'Beban Uang Jalan / Uang Makan Driver', 'type' => 'expense'],
            ['code' => '5200', 'name' => 'Beban Vendor / Borongan', 'type' => 'expense'],
            ['code' => '5210', 'name' => 'Beban Maintenance Kendaraan', 'type' => 'expense'],
            ['code' => '5220', 'name' => 'Beban Sparepart', 'type' => 'expense'],
            ['code' => '5300', 'name' => 'Beban Operasional Langsung Lainnya', 'type' => 'expense'],

            // ====== BEBAN ADMINISTRASI & UMUM (Kepala 6) ======
            ['code' => '6100', 'name' => 'Beban Gaji & Upah Karyawan', 'type' => 'expense'],
            ['code' => '6110', 'name' => 'Beban Pajak & Perizinan', 'type' => 'expense'],
            ['code' => '6120', 'name' => 'Beban ATK & Perlengkapan Kantor', 'type' => 'expense'],
            ['code' => '6140', 'name' => 'Beban Listrik & Air', 'type' => 'expense'],
            ['code' => '6150', 'name' => 'Beban Telepon & Internet', 'type' => 'expense'],
            ['code' => '6160', 'name' => 'Beban Sewa Kantor', 'type' => 'expense'],
            ['code' => '6200', 'name' => 'Beban Umum Lainnya', 'type' => 'expense'],

            // ====== PENDAPATAN & BEBAN LAIN-LAIN (Kepala 7) ======
            // Pendapatan Lain-lain (di luar usaha)
            ['code' => '7100', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue', 'is_postable' => false],
            ['code' => '7110', 'name' => 'Pendapatan Bunga Bank', 'type' => 'revenue'],
            ['code' => '7120', 'name' => 'Pendapatan Selisih Kurs', 'type' => 'revenue'],
            ['code' => '7190', 'name' => 'Pendapatan Lain-lain Lainnya', 'type' => 'revenue'],

            // Beban Lain-lain (di luar usaha)
            ['code' => '7200', 'name' => 'Beban Lain-lain', 'type' => 'expense', 'is_postable' => false],
            ['code' => '7210', 'name' => 'Beban Bunga Pinjaman', 'type' => 'expense'],
            ['code' => '7220', 'name' => 'Beban Provisi Bank', 'type' => 'expense'],
            ['code' => '7230', 'name' => 'Beban Administrasi Bank', 'type' => 'expense'],
            ['code' => '7240', 'name' => 'Beban Pajak Bank', 'type' => 'expense'],
            ['code' => '7250', 'name' => 'Beban Selisih Kurs', 'type' => 'expense'],
            ['code' => '7290', 'name' => 'Beban Lain-lain Lainnya', 'type' => 'expense'],
        ];

        foreach ($rows as $row) {
            DB::table('chart_of_accounts')->updateOrInsert(
                ['code' => $row['code']],
                array_merge([
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'parent_id' => null,
                    'level' => 1,
                    'is_postable' => $row['is_postable'] ?? true,
                    'is_cash' => $row['is_cash'] ?? false,
                    'is_bank' => $row['is_bank'] ?? false,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ], [])
            );
        }
    }
}
