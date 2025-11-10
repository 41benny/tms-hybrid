<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorBill extends Model
{
    protected $fillable = [
        'vendor_id', 'vendor_bill_number', 'bill_date', 'due_date', 'total_amount', 'status', 'notes',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorBillItem::class);
    }
}
