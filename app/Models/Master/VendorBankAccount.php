<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBankAccount extends Model
{
    protected $fillable = [
        'vendor_id',
        'bank_name',
        'account_number',
        'account_holder_name',
        'branch',
        'is_primary',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get formatted account display
     */
    public function getFormattedAccountAttribute(): string
    {
        return "{$this->bank_name} - {$this->account_number} ({$this->account_holder_name})";
    }
}
