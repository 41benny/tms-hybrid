<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model
{
    protected $fillable = [
        'journal_id',
        'account_id',
        'description',
        'debit',
        'credit',
        'job_order_id',
        'transport_id',
        'customer_id',
        'vendor_id',
        'driver_id',
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Driver::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Vendor::class);
    }

    public function transport(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Transport::class);
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\JobOrder::class, 'job_order_id');
    }
}
