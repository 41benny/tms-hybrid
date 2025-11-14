<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegAdditionalCost extends Model
{
    protected $fillable = [
        'shipment_leg_id',
        'cost_type',
        'description',
        'amount',
        'is_billable',
        'billable_amount',
        'vendor_id',
        'pic_name',
        'pic_phone',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_billable' => 'boolean',
            'billable_amount' => 'decimal:2',
        ];
    }

    public function shipmentLeg(): BelongsTo
    {
        return $this->belongsTo(ShipmentLeg::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Vendor::class);
    }
}
