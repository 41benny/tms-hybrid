<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegMainCost extends Model
{
    protected $fillable = [
        'shipment_leg_id',
        'vendor_cost',
        'uang_jalan',
        'bbm',
        'toll',
        'other_costs',
        'ppn',
        'pph23',
        'shipping_line',
        'freight_cost',
        'container_no',
        'insurance_provider',
        'policy_number',
        'insured_value',
        'premium_rate',
        'premium_cost',
        'admin_fee',
        'billable_rate',
        'premium_billable',
    ];

    protected function casts(): array
    {
        return [
            'vendor_cost' => 'decimal:2',
            'uang_jalan' => 'decimal:2',
            'bbm' => 'decimal:2',
            'toll' => 'decimal:2',
            'other_costs' => 'decimal:2',
            'ppn' => 'decimal:2',
            'pph23' => 'decimal:2',
            'freight_cost' => 'decimal:2',
            'insured_value' => 'decimal:2',
            'premium_rate' => 'decimal:2',
            'premium_cost' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'billable_rate' => 'decimal:2',
            'premium_billable' => 'decimal:2',
        ];
    }

    public function shipmentLeg(): BelongsTo
    {
        return $this->belongsTo(ShipmentLeg::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->vendor_cost +
               $this->uang_jalan +
               $this->bbm +
               $this->toll +
               $this->other_costs +
               $this->ppn -
               $this->pph23 +  // PPH 23 dipotong (minus)
               $this->freight_cost +
               $this->premium_cost +  // Insurance premium
               $this->admin_fee;  // Insurance admin fee
    }
}
