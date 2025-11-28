<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverAdvancePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_advance_id',
        'cash_bank_transaction_id',
        'amount_paid',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the driver advance that owns the payment
     */
    public function driverAdvance(): BelongsTo
    {
        return $this->belongsTo(DriverAdvance::class);
    }

    /**
     * Get the cash bank transaction
     */
    public function cashBankTransaction(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\CashBankTransaction::class);
    }
}
