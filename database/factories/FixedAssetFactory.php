<?php

namespace Database\Factories;

use App\Models\FixedAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

class FixedAssetFactory extends Factory
{
    protected $model = FixedAsset::class;

    public function definition(): array
    {
        return [
            'code' => 'FA-'.$this->faker->unique()->numberBetween(100,999),
            'name' => 'Asset Test '.$this->faker->word(),
            'acquisition_date' => now()->subMonths(2)->toDateString(),
            'acquisition_cost' => 10000000,
            'useful_life_months' => 12,
            'residual_value' => 1000000,
            'depreciation_method' => 'straight_line',
            'account_asset_id' => 1,
            'account_accum_id' => 1,
            'account_expense_id' => 1,
            'status' => 'active'
        ];
    }
}
