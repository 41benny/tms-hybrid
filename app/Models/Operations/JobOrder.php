<?php

namespace App\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOrder extends Model
{
    protected $fillable = [
        'customer_id',
        'sales_id',
        'job_number',
        'order_date',
        'service_type',
        'origin',
        'destination',
        'invoice_amount',
        'status',
        'notes',
        'cancel_reason',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'invoice_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Customer::class);
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Sales::class);
    }

    public function shipmentLegs(): HasMany
    {
        return $this->hasMany(ShipmentLeg::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JobOrderItem::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(\App\Models\Finance\InvoiceItem::class);
    }

    public function invoices()
    {
        return $this->hasManyThrough(
            \App\Models\Finance\Invoice::class,
            \App\Models\Finance\InvoiceItem::class,
            'job_order_id', // Foreign key on invoice_items
            'id',           // Foreign key on invoices
            'id',           // Local key on job_orders
            'invoice_id'    // Local key on invoice_items
        )->distinct();
    }

    public function getTotalLegsAttribute(): int
    {
        return $this->shipmentLegs()->count();
    }

    public function getTotalCostAttribute(): float
    {
        return $this->shipmentLegs->sum(function ($leg) {
            return $leg->total_cost;
        });
    }

    /**
     * Total biaya berbasis DPP (tanpa PPN, tanpa pengurangan PPh 23).
     */
    public function getTotalCostDppAttribute(): float
    {
        return $this->shipmentLegs->sum(function ($leg) {
            $mainCost = $leg->mainCost;
            $category = $leg->cost_category;

            $mainDpp = match ($category) {
                'vendor' => (float) ($mainCost->vendor_cost ?? 0),
                'pelayaran' => (float) ($mainCost->freight_cost ?? 0),
                'trucking' => (float) (($mainCost->uang_jalan ?? 0) + ($mainCost->bbm ?? 0) + ($mainCost->toll ?? 0) + ($mainCost->other_costs ?? 0)),
                'asuransi' => (float) (($mainCost->premium_cost ?? 0) + ($mainCost->admin_fee ?? 0)),
                'pic' => (float) ($mainCost->pic_amount ?? 0),
                default => (float) ($mainCost->vendor_cost ?? 0),
            };

            // Jika PPN tidak dikreditkan, bebankan ke biaya
            $ppnExpense = ($mainCost && $mainCost->ppn_noncreditable) ? (float) ($mainCost->ppn ?? 0) : 0;

            $additional = $leg->additionalCosts->sum('amount');
            return $mainDpp + $ppnExpense + $additional;
        });
    }

    public function getTotalBillableAttribute(): float
    {
        return $this->shipmentLegs->sum(function ($leg) {
            $billable = 0;

            // Add premium_billable from insurance leg
            if ($leg->cost_category == 'asuransi' && $leg->mainCost) {
                $billable += $leg->mainCost->premium_billable ?? 0;
            }

            // Add billable additional costs
            $billable += $leg->additionalCosts->where('is_billable', true)->sum('billable_amount');

            return $billable;
        });
    }

    public function getTotalRevenueAttribute(): float
    {
        return $this->invoice_amount + $this->total_billable;
    }

    public function getMarginAttribute(): float
    {
        return $this->total_revenue - $this->total_cost_dpp;
    }

    public function getMarginPercentageAttribute(): float
    {
        if ($this->total_revenue == 0) {
            return 0;
        }

        return ($this->margin / $this->total_revenue) * 100;
    }

    /**
     * Check if job order is locked (completed or cancelled).
     */
    public function isLocked(): bool
    {
        return in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if job order has been invoiced
     */
    public function isInvoiced(): bool
    {
        return $this->invoiceItems()->exists();
    }

    /**
     * Check if job order is fully invoiced (including additional costs)
     */
    public function isFullyInvoiced(): bool
    {
        // Check main invoice amount
        $totalInvoiced = $this->invoiceItems()->sum('amount');
        $expectedAmount = $this->invoice_amount + $this->total_billable;

        return $totalInvoiced >= $expectedAmount;
    }

    /**
     * Get invoice status for display
     */
    public function getInvoiceStatusAttribute(): string
    {
        if (!$this->isInvoiced()) {
            return 'not_invoiced';
        }

        if ($this->isFullyInvoiced()) {
            return 'fully_invoiced';
        }

        return 'partially_invoiced';
    }

    /**
     * Get total invoiced amount
     */
    public function getTotalInvoicedAttribute(): float
    {
        return $this->invoiceItems()->sum('amount');
    }

    /**
     * Get uninvoiced amount
     */
    public function getUninvoicedAmountAttribute(): float
    {
        $expected = $this->invoice_amount + $this->total_billable;
        return max(0, $expected - $this->total_invoiced);
    }
}
