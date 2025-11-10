<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBankTransaction extends Model
{
    protected $fillable = [
        'cash_bank_account_id', 'tanggal', 'jenis', 'sumber', 'invoice_id', 'vendor_bill_id', 'coa_id', 'customer_id', 'vendor_id', 'amount', 'reference_number', 'description',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'amount' => 'decimal:2',
    ];

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
}
