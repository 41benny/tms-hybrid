<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();
        
        $accounts = [
            // SDM & Payroll
            ['code' => '6115', 'name' => 'Beban Lembur', 'type' => 'expense'],
            ['code' => '6117', 'name' => 'Beban Tunjangan Karyawan', 'type' => 'expense'],
            ['code' => '6118', 'name' => 'Beban BPJS Ketenagakerjaan', 'type' => 'expense'],
            ['code' => '6119', 'name' => 'Beban BPJS Kesehatan', 'type' => 'expense'],
            ['code' => '6125', 'name' => 'Beban Komisi & Insentif', 'type' => 'expense'],
            ['code' => '6135', 'name' => 'Beban Pengobatan', 'type' => 'expense'],
            
            // Operasional Kantor
            ['code' => '6131', 'name' => 'Beban Kebersihan', 'type' => 'expense'],
            ['code' => '6132', 'name' => 'Beban Keamanan', 'type' => 'expense'],
            ['code' => '6133', 'name' => 'Beban Representasi & Jamuan', 'type' => 'expense'],
            ['code' => '6134', 'name' => 'Beban Konsumsi & Rumah Tangga Kantor', 'type' => 'expense'],
            
            // Marketing & Umum
            ['code' => '6180', 'name' => 'Beban Iklan & Promosi', 'type' => 'expense'],
            ['code' => '6181', 'name' => 'Beban Sumbangan & CSR', 'type' => 'expense'],
            
            // Pajak & Legal
            ['code' => '6182', 'name' => 'Beban Retribusi', 'type' => 'expense'],
            ['code' => '6183', 'name' => 'Beban Perizinan & Legalitas', 'type' => 'expense'],
            
            // Penyusutan
            ['code' => '6300', 'name' => 'Beban Penyusutan', 'type' => 'expense', 'is_postable' => false],
            ['code' => '6310', 'name' => 'Beban Penyusutan Kendaraan', 'type' => 'expense'],
            ['code' => '6320', 'name' => 'Beban Penyusutan Peralatan', 'type' => 'expense'],
        ];
        
        foreach ($accounts as $account) {
            DB::table('chart_of_accounts')->updateOrInsert(
                ['code' => $account['code']],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'parent_id' => null,
                    'level' => 1,
                    'is_postable' => $account['is_postable'] ?? true,
                    'is_cash' => false,
                    'is_bank' => false,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $codes = ['6115', '6117', '6118', '6119', '6125', '6135', '6131', '6132', '6133', '6134', '6180', '6181', '6182', '6183', '6300', '6310', '6320'];
        
        DB::table('chart_of_accounts')->whereIn('code', $codes)->delete();
    }
};
