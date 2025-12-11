<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    protected $fillable = [
        'journal_no',
        'journal_date',
        'fiscal_period_id',
        'source_type',
        'source_id',
        'memo',
        'status',
        'currency',
        'total_debit',
        'total_credit',
        'posted_by',
        'posted_at',
        'is_revision',
        'original_journal_id',
        'revised_at',
    ];

    protected $casts = [
        'journal_date' => 'date',
        'posted_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(FiscalPeriod::class, 'fiscal_period_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }
}
