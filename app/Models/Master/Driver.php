<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = [
        'name', 'phone', 'is_active', 'vendor_id',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function trucks(): HasMany
    {
        return $this->hasMany(Truck::class);
    }

    public function driverAdvances(): HasMany
    {
        return $this->hasMany(\App\Models\Operations\DriverAdvance::class);
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(\App\Models\Accounting\JournalLine::class);
    }

    public function cashBankTransactions(): HasMany
    {
        return $this->hasMany(\App\Models\Finance\CashBankTransaction::class);
    }
}
