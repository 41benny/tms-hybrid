<?php

namespace Database\Seeders;

use App\Models\Accounting\ChartOfAccount;
use Illuminate\Database\Seeder;

class DriverAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'code' => '2155',
                'name' => 'Hutang Uang Jalan Supir',
                'type' => 'liability',
            ],
            [
                'code' => '2160',
                'name' => 'Hutang Tabungan Supir',
                'type' => 'liability',
            ],
            [
                'code' => '2170',
                'name' => 'Hutang Jaminan Supir',
                'type' => 'liability',
            ],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::updateOrCreate(
                ['code' => $account['code']],
                $account
            );
        }

        $this->command->info('Driver-related accounts added successfully!');
    }
}
