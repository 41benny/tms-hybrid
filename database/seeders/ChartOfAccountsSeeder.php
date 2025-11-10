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
            // Aset
            ['code' => '1100', 'name' => 'Kas', 'type' => 'asset', 'is_cash' => true],
            ['code' => '1110', 'name' => 'Bank', 'type' => 'asset', 'is_bank' => true],
            ['code' => '1200', 'name' => 'Piutang Usaha', 'type' => 'asset'],
            // Kewajiban
            ['code' => '2100', 'name' => 'Hutang Usaha', 'type' => 'liability'],
            ['code' => '2210', 'name' => 'PPN Keluaran', 'type' => 'liability'],
            ['code' => '2220', 'name' => 'PPN Masukan', 'type' => 'asset'],
            // Ekuitas
            ['code' => '3100', 'name' => 'Modal', 'type' => 'equity', 'is_postable' => false],
            ['code' => '3200', 'name' => 'Laba Ditahan', 'type' => 'equity'],
            // Pendapatan
            ['code' => '4100', 'name' => 'Pendapatan Jasa Angkutan', 'type' => 'revenue'],
            // Beban
            ['code' => '5100', 'name' => 'Beban BBM', 'type' => 'expense'],
            ['code' => '5110', 'name' => 'Beban Tol', 'type' => 'expense'],
            ['code' => '5120', 'name' => 'Beban Uang Makan', 'type' => 'expense'],
            ['code' => '5200', 'name' => 'Beban Vendor', 'type' => 'expense'],
            ['code' => '5300', 'name' => 'Beban Operasional Lainnya', 'type' => 'expense'],
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
