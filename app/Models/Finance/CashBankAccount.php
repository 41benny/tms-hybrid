<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashBankAccount extends Model
{
    protected $fillable = [
        'name', 'code', 'is_active',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(CashBankTransaction::class);
    }
}
