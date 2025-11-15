<?php

namespace App\Models\Inventory;

use App\Models\Master\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartPurchase extends Model
{
    protected $fillable = [
        'purchase_number',
        'purchase_date',
        'vendor_id',
        'vendor_bill_id',
        'invoice_number',
        'status',
        'is_direct_usage',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'is_direct_usage' => 'boolean',
        'total_amount' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PartPurchaseItem::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PartUsage::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendorBill(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\VendorBill::class);
    }
}
