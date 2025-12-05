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
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revision_number' => 'integer',
        'revised_at' => 'datetime',
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

    public function transactionPayments(): HasMany
    {
        return $this->hasMany(InvoiceTransactionPayment::class);
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
    
    public function relatedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'related_invoice_id');
    }
    
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function originalInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'original_invoice_id');
    }

    public function revisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Invoice::class, 'original_invoice_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Accounting\Journal::class, 'journal_id');
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

    public function scopeUnposted($query)
    {
        return $query->whereIn('status', ['sent', 'partial', 'paid'])
            ->whereNull('journal_id');
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
        // Can only edit if status is draft AND approval status is draft or rejected
        return $this->status === 'draft' && in_array($this->approval_status, ['draft', 'rejected']);
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
    
    public function isDownPayment(): bool
    {
        return $this->invoice_type === 'down_payment';
    }
    
    public function isFinal(): bool
    {
        return $this->invoice_type === 'final';
    }
    
    public function isNormal(): bool
    {
        return $this->invoice_type === 'normal';
    }
    
    public function canBeSubmittedForApproval(): bool
    {
        return $this->approval_status === 'draft' 
            && $this->items()->count() > 0 
            && $this->total_amount > 0;
    }
    
    public function canBeApproved(): bool
    {
        return $this->approval_status === 'pending_approval';
    }
    
    public function canBePrinted(): bool
    {
        return $this->approval_status === 'approved';
    }
    
    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending_approval';
    }
    
    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }
    
    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }
    
    // Revision methods
    public function canBeRevised(): bool
    {
        // Cannot revise if paid
        if ($this->status === 'paid') {
            return false;
        }
        
        // Cannot revise draft (use normal edit)
        if ($this->approval_status === 'draft') {
            return false;
        }
        
        // Cannot revise pending (reject first)
        if ($this->approval_status === 'pending_approval') {
            return false;
        }
        
        return true; // approved or rejected can be revised
    }
    
    public function isAccountingPeriodClosed(): bool
    {
        // Check if accounting period for this invoice date is closed
        $period = \App\Models\Accounting\AccountingPeriod::where('year', $this->invoice_date->year)
            ->where('month', $this->invoice_date->month)
            ->first();
        
        return $period && $period->is_closed;
    }
    
    public function getBaseInvoiceNumber(): string
    {
        // Remove revision suffix if exists
        // INV-202511-0001-001 -> INV-202511-0001
        $parts = explode('-', $this->invoice_number);
        if (count($parts) === 4) {
            // Has revision, remove last part
            array_pop($parts);
            return implode('-', $parts);
        }
        return $this->invoice_number;
    }
    
    public function getNextRevisionNumber(): string
    {
        $base = $this->getBaseInvoiceNumber();
        $nextRevision = $this->revision_number + 1;
        return $base . '-' . str_pad($nextRevision, 3, '0', STR_PAD_LEFT);
    }
    
    public function isRevision(): bool
    {
        return $this->revision_number > 0;
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
            
            // Generate signature: XXXX-XXXX-XXXX format
            if (empty($invoice->signature)) {
                $invoice->signature = strtoupper(\Illuminate\Support\Str::random(4)) . '-' . 
                                     strtoupper(\Illuminate\Support\Str::random(4)) . '-' . 
                                     strtoupper(\Illuminate\Support\Str::random(4));
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
