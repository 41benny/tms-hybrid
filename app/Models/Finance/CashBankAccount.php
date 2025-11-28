<?php

namespace App\Models\Finance;

use App\Models\Accounting\ChartOfAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashBankAccount extends Model
{
    protected $fillable = [
        'name',
        'code',
        'bank_code',
        'type',
        'account_number',
        'bank_name',
        'branch',
        'account_holder',
        'coa_id',
        'opening_balance',
        'current_balance',
        'description',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function transactions(): HasMany
    {
        return $this->hasMany(CashBankTransaction::class);
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCash($query)
    {
        return $query->where('type', 'cash');
    }

    public function scopeBank($query)
    {
        return $query->where('type', 'bank');
    }

    // Helper Methods
    public function updateBalance(float $amount, string $type = 'in'): void
    {
        if ($type === 'in') {
            $this->current_balance += $amount;
        } else {
            $this->current_balance -= $amount;
        }
        $this->save();
    }

    public function isCash(): bool
    {
        return $this->type === 'cash';
    }

    public function isBank(): bool
    {
        return $this->type === 'bank';
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->isBank() && $this->bank_name) {
            return $this->name . ' - ' . $this->bank_name . ' (' . $this->account_number . ')';
        }
        return $this->name;
    }
}
