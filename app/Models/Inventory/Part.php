<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    protected $fillable = [
        'code',
        'name',
        'unit',
        'category',
        'min_stock',
        'description',
        'is_active',
    ];

    protected $casts = [
        'min_stock' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(PartStock::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PartPurchaseItem::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PartUsage::class);
    }

    public function getTotalStockAttribute(): float
    {
        return $this->stocks()->sum('quantity');
    }
}
