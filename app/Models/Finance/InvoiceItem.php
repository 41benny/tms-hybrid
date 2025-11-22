<?php

namespace App\Models\Finance;

use App\Models\Operations\JobOrder;
use App\Models\Operations\ShipmentLeg;
use App\Models\Operations\Transport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'exclude_tax' => 'boolean',
    ];
    
    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
    
    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }
    
    public function shipmentLeg(): BelongsTo
    {
        return $this->belongsTo(ShipmentLeg::class);
    }
    
    public function transport(): BelongsTo
    {
        return $this->belongsTo(Transport::class);
    }
    
    // Boot method
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($item) {
            $item->amount = $item->quantity * $item->unit_price;
        });
        
        static::saved(function ($item) {
            if ($item->invoice) {
                $item->invoice->recalculateTotals();
            }
        });
        
        static::deleted(function ($item) {
            if ($item->invoice) {
                $item->invoice->recalculateTotals();
            }
        });
    }
}
