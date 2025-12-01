<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegMainCost extends Model
{
    protected $fillable = [
        'shipment_leg_id',
        'vendor_id',
        'pic_name',
        'pic_phone',
        'vendor_cost',
        'uang_jalan',
        'driver_savings_deduction',
        'driver_guarantee_deduction',
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
        'cost_type',
        'pic_amount',
        'pic_notes',
        'ppn_noncreditable',
    ];

    protected function casts(): array
    {
        return [
            'vendor_cost' => 'decimal:2',
            'uang_jalan' => 'decimal:2',
            'driver_savings_deduction' => 'decimal:2',
            'driver_guarantee_deduction' => 'decimal:2',
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
            'pic_amount' => 'decimal:2',
            'ppn_noncreditable' => 'boolean',
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
               $this->admin_fee +  // Insurance admin fee
               $this->pic_amount;  // PIC payment
    }

    /**
     * Base cost (DPP) per leg category, tanpa PPN dan tanpa pengurangan PPh 23.
     */
    public function getDppAttribute(): float
    {
        // Hindari lazy loading: pakai cost_category hanya jika relasi sudah diload
        $category = $this->relationLoaded('shipmentLeg')
            ? $this->shipmentLeg?->cost_category
            : null;

        return match ($category) {
            'vendor' => (float) $this->vendor_cost,
            'pelayaran' => (float) $this->freight_cost,
            'trucking' => (float) ($this->uang_jalan + $this->bbm + $this->toll + $this->other_costs),
            'asuransi' => (float) ($this->premium_cost + $this->admin_fee),
            'pic' => (float) $this->pic_amount,
            default => (float) $this->vendor_cost,
        };
    }

    /**
     * Get net uang jalan (after deductions)
     */
    public function getNetUangJalanAttribute(): float
    {
        $gross = (float) $this->uang_jalan;
        $savings = (float) $this->driver_savings_deduction;
        $guarantee = (float) $this->driver_guarantee_deduction;
        
        return $gross - $savings - $guarantee;
    }

    /**
     * Get total deductions
     */
    public function getTotalDeductionsAttribute(): float
    {
        return (float) $this->driver_savings_deduction + (float) $this->driver_guarantee_deduction;
    }
}
