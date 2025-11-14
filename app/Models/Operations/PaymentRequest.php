<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRequest extends Model
{
    protected $fillable = [
        'vendor_bill_id',
        'vendor_id',
        'vendor_bank_account_id',
        'payment_type',
        'description',
        'requested_by',
        'request_number',
        'request_date',
        'amount',
        'status',
        'notes',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'cash_bank_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function vendorBill(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\VendorBill::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Vendor::class);
    }

    public function vendorBankAccount(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\VendorBankAccount::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'paid_by');
    }

    public function cashBankTransaction(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\CashBankTransaction::class);
    }
}
