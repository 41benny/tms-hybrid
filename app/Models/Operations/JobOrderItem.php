<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderItem extends Model
{
    protected $fillable = [
        'job_order_id',
        'equipment_id',
        'cargo_type',
        'quantity',
        'price',
        'serial_numbers',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'price' => 'decimal:2',
        ];
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Equipment::class);
    }
}
