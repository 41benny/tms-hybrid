<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FiscalPeriodsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $year = (int) $now->format('Y');
        $month = (int) $now->format('m');

        $start = $now->copy()->startOfMonth()->toDateString();
        $end = $now->copy()->endOfMonth()->toDateString();

        DB::table('fiscal_periods')->updateOrInsert(
            ['year' => $year, 'month' => $month],
            [
                'start_date' => $start,
                'end_date' => $end,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
