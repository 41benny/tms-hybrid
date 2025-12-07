<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBankTransaction extends Model
{
    protected $fillable = [
        'voucher_number',
        'cash_bank_account_id',
        'tanggal',
        'jenis',
        'sumber',
        'invoice_id',
        'vendor_bill_id',
        'coa_id',
        'customer_id',
        'vendor_id',
        'amount',
        'admin_fee',
        'withholding_pph23',
        'reference_number',
        'recipient_name',
        'description',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'withholding_pph23' => 'decimal:2',
        'voided_at' => 'datetime',
    ];
    
    public function isVoided(): bool
    {
        return !is_null($this->voided_at);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CashBankAccount::class, 'cash_bank_account_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\Invoice::class);
    }

    public function vendorBill(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\VendorBill::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Vendor::class);
    }

    public function accountCoa(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Accounting\ChartOfAccount::class, 'coa_id');
    }

    public function invoicePayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceTransactionPayment::class);
    }

    public function driverAdvancePayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Operations\DriverAdvancePayment::class);
    }

    public function vendorBillPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VendorBillPayment::class);
    }
}
