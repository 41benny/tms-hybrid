<?php

namespace App\Models\Accounting;

use App\Models\Finance\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxInvoiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'invoice_id',
        'transaction_type',
        'customer_name',
        'customer_npwp',
        'dpp',
        'ppn',
        'total_amount',
        'description',
        'status',
        'requested_by',
        'requested_at',
        'tax_invoice_number',
        'tax_invoice_date',
        'tax_invoice_file_path',
        'completed_by',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'dpp' => 'decimal:2',
        'ppn' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
        'tax_invoice_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Scopes
     */
    public function scopeRequested($query)
    {
        return $query->where('status', 'requested');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByPeriod($query, $from, $to)
    {
        return $query->whereBetween('requested_at', [$from, $to]);
    }

    /**
     * Generate request number
     */
    public static function generateRequestNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        
        $lastRequest = self::whereYear('requested_at', $year)
            ->whereMonth('requested_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastRequest ? (int) substr($lastRequest->request_number, -4) + 1 : 1;
        
        return sprintf('TIR-%s-%s-%04d', $year, $month, $sequence);
    }

    /**
     * Check if request is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if request is pending
     */
    public function isRequested(): bool
    {
        return $this->status === 'requested';
    }
}
