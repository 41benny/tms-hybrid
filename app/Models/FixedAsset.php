<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class FixedAsset extends Model
{
    protected $fillable = [
        'code', 'name', 'acquisition_date', 'acquisition_cost', 'useful_life_months',
        'residual_value', 'depreciation_method', 'account_asset_id', 'account_accum_id',
        'account_expense_id', 'status'
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'residual_value' => 'decimal:2'
    ];

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    public function disposals(): HasMany
    {
        return $this->hasMany(AssetDisposal::class);
    }

    public function accumulatedDepreciation(): float
    {
        return (float) $this->depreciations()->sum('amount');
    }

    public function bookValue(): float
    {
        return (float) ($this->acquisition_cost - $this->accumulatedDepreciation());
    }
}
