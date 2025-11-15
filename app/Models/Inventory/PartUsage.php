<?php

namespace App\Models\Inventory;

use App\Models\Master\Truck;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartUsage extends Model
{
    protected $fillable = [
        'usage_number',
        'usage_date',
        'part_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'truck_id',
        'usage_type',
        'description',
        'part_purchase_id',
        'created_by',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(PartPurchase::class, 'part_purchase_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
