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
        'journal_status',
        'journal_id',
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
            'journal_status' => 'string',
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

    /**
     * Get total amount that has been requested via payment requests
     */
    public function getTotalRequestedAttribute(): float
    {
        // Use loaded relation to avoid N+1 queries
        if ($this->relationLoaded('paymentRequests')) {
            return (float) $this->paymentRequests->sum('amount');
        }

        return (float) $this->paymentRequests()->sum('amount');
    }

    /**
     * Get remaining amount that can still be requested
     */
    public function getRemainingToRequestAttribute(): float
    {
        $remaining = (float) $this->amount - $this->total_requested;
        return $remaining > 0 ? $remaining : 0.0;
    }

    public function shipmentLeg(): BelongsTo
    {
        return $this->belongsTo(ShipmentLeg::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Driver::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Accounting\Journal::class);
    }

    public function paymentRequests()
    {
        return $this->hasMany(PaymentRequest::class, 'driver_advance_id');
    }

    protected static function booted(): void
    {
        static::creating(function (DriverAdvance $advance) {
            // Auto-fill notes if empty OR if it's the old auto-generated format
            if (empty($advance->notes) || str_starts_with($advance->notes ?? '', 'Auto-generated from Leg')) {
                $advance->notes = $advance->generateAutoDescription();
            }
        });
    }

    /**
     * Generate automatic description for notes.
     * Format:
     * Uang jalan (driverName plateNumber) dari origin tujuan destination muat qty costCategory order customerName
     */
    public function generateAutoDescription(): string
    {
        // Ensure related data is available
        $driverName = $this->driver?->name;
        if (!$driverName && $this->driver_id) {
            $driverName = \App\Models\Master\Driver::find($this->driver_id)?->name;
        }

        $shipmentLeg = $this->shipmentLeg;
        if (!$shipmentLeg && $this->shipment_leg_id) {
            $shipmentLeg = ShipmentLeg::with(['truck','jobOrder.customer'])->find($this->shipment_leg_id);
        }

        $plate = $shipmentLeg?->truck?->plate_number;
        $origin = $shipmentLeg?->jobOrder?->origin;
        $destination = $shipmentLeg?->jobOrder?->destination;
        $qty = $shipmentLeg?->quantity; // decimal:2
        $costCategory = $shipmentLeg?->cost_category;
        $customerName = $shipmentLeg?->jobOrder?->customer?->name;

        // Format quantity: drop trailing ,00
        $qtyFormatted = '-';
        if ($qty !== null) {
            $qtyFormatted = number_format($qty, 2, ',', '.');
            // Remove trailing ,00 or ,0
            $qtyFormatted = preg_replace('/,(00|0)$/', '', $qtyFormatted);
        }

        $parts = [
            'Uang jalan (' . trim(trim($driverName ?: '-') . ' ' . ($plate ?: '-')) . ')',
            'dari ' . ($origin ?: '-'),
            'tujuan ' . ($destination ?: '-'),
            'muat ' . $qtyFormatted . ($costCategory ? ' ' . $costCategory : ''),
            'order ' . ($customerName ?: '-')
        ];

        return trim(implode(' ', $parts));
    }

    public function getAutoDescriptionAttribute(): string
    {
        return $this->generateAutoDescription();
    }

    /**
     * Scope: Driver advances that still have remaining amount to be requested
     * Status is pending or dp_paid and total_requested < amount
     */
    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['pending', 'dp_paid'])
            ->whereRaw('(amount - (SELECT COALESCE(SUM(amount),0) FROM payment_requests WHERE driver_advance_id = driver_advances.id)) > 0');
    }

    /**
     * Scope: Driver advances that have been fully requested but not yet settled
     */
    public function scopeFullyRequested($query)
    {
        return $query->whereIn('status', ['pending', 'dp_paid'])
            ->whereRaw('(amount - (SELECT COALESCE(SUM(amount),0) FROM payment_requests WHERE driver_advance_id = driver_advances.id)) = 0');
    }
}
