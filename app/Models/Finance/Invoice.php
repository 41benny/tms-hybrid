<?php

namespace App\Models\Finance;

use App\Models\Accounting\TaxInvoiceRequest;
use App\Models\Master\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'pph23_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'show_pph23' => 'boolean',
        'tax_requested_at' => 'datetime',
        'tax_completed_at' => 'datetime',
        'tax_invoice_date' => 'date',
    ];
    
    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function transport(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Transport::class);
    }
    
    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(PaymentReceipt::class, 'invoice_payments')
                    ->withPivot('allocated_amount', 'created_by')
                    ->withTimestamps();
    }
    
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    public function taxInvoiceRequest(): HasOne
    {
        return $this->hasOne(TaxInvoiceRequest::class);
    }
    
    public function taxRequester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tax_requested_by');
    }
    
    public function taxCompleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tax_completed_by');
    }
    
    // Scopes
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
                    ->where('due_date', '<', now());
    }
    
    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['pending', 'sent', 'partial', 'overdue']);
    }
    
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
    
    public function scopeNeedsTaxInvoice($query)
    {
        return $query->where('status', '!=', 'cancelled')
                    ->where('tax_invoice_status', 'none');
    }
    
    public function scopeTaxRequested($query)
    {
        return $query->where('tax_invoice_status', 'requested');
    }
    
    public function scopeTaxCompleted($query)
    {
        return $query->where('tax_invoice_status', 'completed');
    }
    
    // Helper Methods
    public function getOutstandingAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }
    
    public function isOverdue(): bool
    {
        return !in_array($this->status, ['paid', 'cancelled']) 
            && $this->due_date < now();
    }
    
    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }
    
    public function canBeCancelled(): bool
    {
        return !in_array($this->status, ['paid', 'cancelled']);
    }
    
    public function updatePaidAmount(): void
    {
        $this->paid_amount = $this->payments()
            ->sum('invoice_payments.allocated_amount');
        
        // Auto-update status
        if ($this->paid_amount >= $this->total_amount) {
            $this->status = 'paid';
            $this->paid_at = now();
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        }
        
        $this->save();
    }
    
    public function markAsSent(): void
    {
        if ($this->status === 'draft') {
            $this->status = 'sent';
            $this->sent_at = now();
            $this->save();
        }
    }
    
    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('amount');
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->saveQuietly(); // Use saveQuietly to avoid triggering boot events again
    }
    
    public static function generateNumber(): string
    {
        $year = now()->year;
        $lastInvoice = static::whereYear('created_at', $year)
                            ->latest('id')
                            ->first();
        
        $nextNumber = $lastInvoice 
            ? intval(substr($lastInvoice->invoice_number, -4)) + 1 
            : 1;
        
        return sprintf('INV-%d-%04d', $year, $nextNumber);
    }
    
    // Boot method
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateNumber();
            }
            
            // Only set created_by if auth is available and user is logged in
            if (app()->bound('auth') && auth()->hasUser()) {
                $invoice->created_by = auth()->id();
            }
        });
        
        static::updating(function ($invoice) {
            // Only set updated_by if auth is available and user is logged in
            if (app()->bound('auth') && auth()->hasUser()) {
                $invoice->updated_by = auth()->id();
            }
        });
    }
}
