<?php

namespace Database\Seeders;

use App\Models\Operations\DriverAdvance;
use Illuminate\Database\Seeder;

class FixDriverAdvanceDpSeeder extends Seeder
{
    public function run(): void
    {
        // Fix driver advances yang dp_amount-nya tercatat 2x
        // karena void transaction tidak ter-rollback
        
        $fixes = [
            // Khairul (ID 2) - seharusnya 1.800.000 (bukan 3.600.000)
            ['advance_number' => 'ADV-202511-0002', 'correct_dp' => 1800000],
            
            // Ronal Regen (ID 1) - seharusnya 2.200.000 (bukan 4.400.000)
            ['advance_number' => 'ADV-202511-0001', 'correct_dp' => 2200000],
        ];
        
        foreach ($fixes as $fix) {
            $advance = DriverAdvance::where('advance_number', $fix['advance_number'])->first();
            
            if ($advance) {
                $oldDp = $advance->dp_amount;
                $advance->update(['dp_amount' => $fix['correct_dp']]);
                
                $this->command->info("Fixed {$fix['advance_number']}: {$oldDp} → {$fix['correct_dp']}");
            } else {
                $this->command->warn("Driver advance {$fix['advance_number']} not found");
            }
        }
        
        $this->command->info('Driver advance DP amounts fixed successfully!');
    }
}
