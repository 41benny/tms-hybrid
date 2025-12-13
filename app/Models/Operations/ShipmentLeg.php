<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShipmentLeg extends Model
{
    protected $fillable = [
        'job_order_id',
        'leg_number',
        'leg_code',
        'cost_category',
        'executor_type',
        'vendor_id',
        'truck_id',
        'driver_id',
        'equipment_id',
        'vessel_name',
        'load_date',
        'unload_date',
        'quantity',
        'serial_numbers',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'load_date' => 'date',
            'unload_date' => 'date',
            'quantity' => 'decimal:2',
        ];
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Vendor::class);
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Truck::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Driver::class)->withDefault();
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Equipment::class);
    }

    public function mainCost(): HasOne
    {
        return $this->hasOne(LegMainCost::class);
    }

    public function additionalCosts(): HasMany
    {
        return $this->hasMany(LegAdditionalCost::class);
    }

    public function vendorBillItems(): HasMany
    {
        return $this->hasMany(\App\Models\Finance\VendorBillItem::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(\App\Models\Finance\InvoiceItem::class);
    }

    public function driverAdvance(): HasOne
    {
        return $this->hasOne(\App\Models\Operations\DriverAdvance::class);
    }

    public function getTotalCostAttribute(): float
    {
        $mainTotal = $this->mainCost ? $this->mainCost->total : 0;
        $additionalTotal = $this->additionalCosts->sum('amount');

        return $mainTotal + $additionalTotal;
    }
}
