<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBillItem extends Model
{
    protected $fillable = [
        'vendor_bill_id', 'transport_id', 'shipment_leg_id', 'description', 'qty', 'unit_price', 'subtotal',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function vendorBill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class);
    }

    public function shipmentLeg(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\ShipmentLeg::class);
    }

    public function transport(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Transport::class);
    }
}
