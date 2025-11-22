<?php

namespace App\Models\Finance;

use App\Models\Finance\CashBankAccount;
use App\Models\Master\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentReceipt extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
    ];
    
    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_payments')
                    ->withPivot('allocated_amount', 'created_by')
                    ->withTimestamps();
    }
    
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(CashBankAccount::class, 'bank_account_id');
    }
    
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
    
    // Helper Methods
    public function getUnallocatedAmountAttribute(): float
    {
        return $this->amount - $this->allocated_amount;
    }
    
    public function isFullyAllocated(): bool
    {
        return $this->allocated_amount >= $this->amount;
    }
    
    public function canAllocate(float $amount): bool
    {
        return ($this->allocated_amount + $amount) <= $this->amount;
    }
    
    public function allocateToInvoice(Invoice $invoice, float $amount): bool
    {
        // Validation
        $availableAmount = $this->amount - $this->allocated_amount;
        $invoiceOutstanding = $invoice->total_amount - $invoice->paid_amount;
        
        $allocateAmount = min($amount, $availableAmount, $invoiceOutstanding);
        
        if ($allocateAmount <= 0) {
            return false;
        }
        
        // Create allocation
        $this->invoices()->attach($invoice->id, [
            'allocated_amount' => $allocateAmount,
            'created_by' => auth()->id(),
        ]);
        
        // Update both records
        $this->allocated_amount += $allocateAmount;
        $this->save();
        
        $invoice->updatePaidAmount();
        
        return true;
    }
    
    public function deallocateFromInvoice(Invoice $invoice): bool
    {
        $allocation = $this->invoices()
            ->where('invoice_id', $invoice->id)
            ->first();
        
        if (!$allocation) {
            return false;
        }
        
        $allocatedAmount = $allocation->pivot->allocated_amount;
        
        // Remove allocation
        $this->invoices()->detach($invoice->id);
        
        // Update both records
        $this->allocated_amount -= $allocatedAmount;
        $this->save();
        
        $invoice->updatePaidAmount();
        
        return true;
    }
    
    public static function generateNumber(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->latest('id')->first();
        $next = $last ? intval(substr($last->receipt_number, -4)) + 1 : 1;
        return sprintf('PMT-%d-%04d', $year, $next);
    }
    
    // Boot method
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($receipt) {
            if (empty($receipt->receipt_number)) {
                $receipt->receipt_number = static::generateNumber();
            }
            if (empty($receipt->received_by)) {
                $receipt->received_by = auth()->id();
            }
        });
    }
}
