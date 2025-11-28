<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorBill extends Model
{
    protected $fillable = [
        'vendor_id', 'vendor_bill_number', 'bill_date', 'due_date', 'total_amount', 'amount_paid', 'status', 'notes',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];
    
    protected $appends = ['outstanding_balance'];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorBillItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(VendorBillPayment::class)
            ->orderBy('payment_date', 'desc');
    }
    
    public function cashBankTransactions(): HasMany
    {
        return $this->hasMany(CashBankTransaction::class, 'vendor_bill_id')
            ->where('sumber', 'vendor_payment')
            ->orderBy('tanggal', 'desc');
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(\App\Models\Operations\PaymentRequest::class, 'vendor_bill_id')
            ->orderBy('request_date', 'desc');
    }
    
    // Computed Properties
    public function getOutstandingBalanceAttribute(): float
    {
        return (float) ($this->total_amount - $this->amount_paid);
    }
    
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->amount_paid >= $this->total_amount;
    }

    protected static function booted(): void
    {
        static::creating(function (VendorBill $bill) {
            // Auto-fill notes if empty OR if it's the old auto-generated format
            if (empty($bill->notes) || str_starts_with($bill->notes ?? '', 'Auto-generated from')) {
                $bill->notes = $bill->generateAutoDescription();
            }
        });
    }

    /**
     * Generate automatic description for vendor bill.
     * Format: "Pembayaran hutang vendor (VendorName) dari origin tujuan destination muat qty costCategory order customerName"
     */
    public function generateAutoDescription(): string
    {
        $vendorName = $this->vendor?->name ?: '-';
        if (!$this->vendor && $this->vendor_id) {
            $vendorName = \App\Models\Master\Vendor::find($this->vendor_id)?->name ?: '-';
        }

        // Get first item's leg data for origin/destination/qty/category/customer
        $firstItem = $this->relationLoaded('items') ? $this->items->first() : $this->items()->with('shipmentLeg.jobOrder.customer')->first();
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

    public function getAutoDescriptionAttribute(): string
    {
        return $this->generateAutoDescription();
    }

    public function getTotalRequestedAttribute(): float
    {
        // Use loaded relation to avoid N+1 queries
        if ($this->relationLoaded('paymentRequests')) {
            return (float) $this->paymentRequests->sum('amount');
        }

        return (float) $this->paymentRequests()->sum('amount');
    }

    public function getRemainingToRequestAttribute(): float
    {
        $remaining = (float) $this->total_amount - $this->total_requested;
        return $remaining > 0 ? $remaining : 0.0;
    }

    public function getRemainingAttribute(): float
    {
        // Alias for remaining_to_request for backward compatibility
        return $this->remaining_to_request;
    }

    // Scopes
    public function scopeOutstanding($query)
    {
        // Status bukan paid/cancelled dan masih ada sisa yang belum diajukan
        return $query->whereNotIn('status', ['paid', 'cancelled'])
            ->whereRaw('(total_amount - (SELECT COALESCE(SUM(amount),0) FROM payment_requests WHERE vendor_bill_id = vendor_bills.id)) > 0');
    }

    public function scopeFullyRequested($query)
    {
        // Sudah diajukan penuh (sisa 0) tapi belum dibayar
        return $query->whereNotIn('status', ['paid', 'cancelled'])
            ->whereRaw('(total_amount - (SELECT COALESCE(SUM(amount),0) FROM payment_requests WHERE vendor_bill_id = vendor_bills.id)) = 0');
    }
}
