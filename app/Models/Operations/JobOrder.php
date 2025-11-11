<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOrder extends Model
{
    protected $fillable = [
        'customer_id',
        'sales_id',
        'job_number',
        'order_date',
        'service_type',
        'origin',
        'destination',
        'invoice_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'invoice_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Customer::class);
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Sales::class);
    }

    public function shipmentLegs(): HasMany
    {
        return $this->hasMany(ShipmentLeg::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JobOrderItem::class);
    }

    public function getTotalLegsAttribute(): int
    {
        return $this->shipmentLegs()->count();
    }

    public function getTotalCostAttribute(): float
    {
        return $this->shipmentLegs->sum(function ($leg) {
            return $leg->total_cost;
        });
    }

    public function getTotalBillableAttribute(): float
    {
        return $this->shipmentLegs->sum(function ($leg) {
            $billable = 0;

            // Add premium_billable from insurance leg
            if ($leg->cost_category == 'asuransi' && $leg->mainCost) {
                $billable += $leg->mainCost->premium_billable ?? 0;
            }

            // Add billable additional costs
            $billable += $leg->additionalCosts->where('is_billable', true)->sum('billable_amount');

            return $billable;
        });
    }

    public function getTotalRevenueAttribute(): float
    {
        return $this->invoice_amount + $this->total_billable;
    }

    public function getMarginAttribute(): float
    {
        return $this->total_revenue - $this->total_cost;
    }

    public function getMarginPercentageAttribute(): float
    {
        if ($this->total_revenue == 0) {
            return 0;
        }

        return ($this->margin / $this->total_revenue) * 100;
    }
}
