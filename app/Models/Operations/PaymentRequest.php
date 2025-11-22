<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRequest extends Model
{
    protected $fillable = [
        'vendor_bill_id',
        'driver_advance_id',
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

    public function driverAdvance(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\DriverAdvance::class);
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentRequest $pr) {
            // Auto-fill notes if empty OR if it's the old auto-generated format
            if (empty($pr->notes) || str_starts_with($pr->notes ?? '', 'Pengajuan pembayaran')) {
                $pr->notes = $pr->generateAutoDescription();
            }
        });
    }

    /**
     * Generate automatic description for payment request notes.
     * Format: "Pembayaran hutang vendor (VendorName) dari origin tujuan destination muat qty costCategory order customerName"
     */
    public function generateAutoDescription(): string
    {
        // Get vendor name
        $vendorName = '-';
        if ($this->payment_type === 'vendor_bill' && $this->vendorBill) {
            $vendorName = $this->vendorBill->vendor?->name ?: '-';
            
            // Get leg data from first vendor bill item
            $firstItem = $this->vendorBill->items()->with('shipmentLeg.jobOrder.customer')->first();
            $leg = $firstItem?->shipmentLeg;
            $jobOrder = $leg?->jobOrder;
            
            $origin = $jobOrder?->origin ?: '-';
            $destination = $jobOrder?->destination ?: '-';
            $qty = $leg?->quantity;
            $costCategory = $leg?->cost_category ?: '';
            $customerName = $jobOrder?->customer?->name ?: '-';
            
            // Format quantity
            $qtyFormatted = '-';
            if ($qty !== null) {
                $qtyFormatted = number_format($qty, 2, ',', '.');
                $qtyFormatted = preg_replace('/,(00|0)$/', '', $qtyFormatted);
            }
            
            $parts = [
                'Pembayaran hutang vendor (' . $vendorName . ')',
                'dari ' . $origin,
                'tujuan ' . $destination,
                'muat ' . $qtyFormatted . ($costCategory ? ' ' . $costCategory : ''),
                'order ' . $customerName
            ];
            
            return trim(implode(' ', $parts));
        }

        // For manual payment without vendor bill
        $vendorName = $this->vendor?->name ?: '-';
        return trim("Pembayaran hutang vendor ({$vendorName}) manual");
    }

    public function getAutoDescriptionAttribute(): string
    {
        return $this->generateAutoDescription();
    }
}
