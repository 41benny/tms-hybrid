<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverAdvance extends Model
{
    protected $fillable = [
        'shipment_leg_id',
        'driver_id',
        'advance_number',
        'advance_date',
        'amount',
        'dp_amount',
        'dp_paid_date',
        'status',
        'paid_date',
        'deduction_savings',
        'deduction_guarantee',
        'settlement_date',
        'settlement_notes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'advance_date' => 'date',
            'paid_date' => 'date',
            'dp_paid_date' => 'date',
            'settlement_date' => 'date',
            'amount' => 'decimal:2',
            'dp_amount' => 'decimal:2',
            'deduction_savings' => 'decimal:2',
            'deduction_guarantee' => 'decimal:2',
        ];
    }

    /**
     * Get remaining amount (sisa yang belum dibayar)
     */
    public function getRemainingAmountAttribute(): float
    {
        return (float) $this->amount - (float) $this->dp_amount;
    }

    /**
     * Get final settlement amount (pelunasan setelah potongan)
     */
    public function getFinalSettlementAttribute(): float
    {
        $remaining = $this->remaining_amount;
        $deductions = (float) $this->deduction_savings + (float) $this->deduction_guarantee;

        return $remaining - $deductions;
    }

    /**
     * Get total deductions
     */
    public function getTotalDeductionsAttribute(): float
    {
        return (float) $this->deduction_savings + (float) $this->deduction_guarantee;
    }

    public function shipmentLeg(): BelongsTo
    {
        return $this->belongsTo(ShipmentLeg::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Driver::class);
    }
}
