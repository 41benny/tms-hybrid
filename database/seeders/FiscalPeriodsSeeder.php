<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FiscalPeriodsSeeder extends Seeder
{
    public function run(): void
    {
        $startPoint = now()->startOfMonth();
        // Seed 12 months forward (including current)
        for ($i = 0; $i < 12; $i++) {
            $current = $startPoint->copy()->addMonths($i);
            $year = (int) $current->format('Y');
            $month = (int) $current->format('m');
            $start = $current->copy()->startOfMonth()->toDateString();
            $end = $current->copy()->endOfMonth()->toDateString();

            DB::table('fiscal_periods')->updateOrInsert(
                ['year' => $year, 'month' => $month],
                [
                    'start_date' => $start,
                    'end_date' => $end,
                    'status' => DB::table('fiscal_periods')->where('year',$year)->where('month',$month)->value('status') ?? 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
