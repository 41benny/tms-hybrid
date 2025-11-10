<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderItem extends Model
{
    protected $fillable = [
        'job_order_id', 'equipment_id', 'equipment_name', 'serial_number', 'qty',
        'origin_route_id', 'destination_route_id', 'origin_text', 'destination_text', 'remark',
    ];

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Equipment::class);
    }

    public function originRoute(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Route::class, 'origin_route_id');
    }

    public function destinationRoute(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Route::class, 'destination_route_id');
    }
}
