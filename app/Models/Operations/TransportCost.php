<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportCost extends Model
{
    protected $fillable = [
        'transport_id', 'cost_category', 'description', 'amount', 'is_vendor_cost',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_vendor_cost' => 'boolean',
    ];

    public function transport(): BelongsTo
    {
        return $this->belongsTo(Transport::class);
    }
}
