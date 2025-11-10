<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transport extends Model
{
    protected $fillable = [
        'job_order_id', 'job_order_item_id', 'executor_type', 'truck_id', 'driver_id', 'vendor_id',
        'departure_date', 'arrival_date', 'status', 'spj_number', 'notes',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'arrival_date' => 'date',
    ];

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function jobOrderItem(): BelongsTo
    {
        return $this->belongsTo(JobOrderItem::class);
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Truck::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Driver::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Vendor::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(TransportCost::class);
    }
}
