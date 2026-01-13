<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'cost_center_id',
        'debit',
        'credit',
        'memo',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (JournalEntryLine $line) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;

            if (($debit > 0 && $credit > 0) || ($debit <= 0 && $credit <= 0)) {
                throw ValidationException::withMessages([
                    'lines' => 'كل سطر يجب أن يحتوي إمّا مدين فقط أو دائن فقط.',
                ]);
            }

            if ($debit < 0 || $credit < 0) {
                throw ValidationException::withMessages([
                    'lines' => 'قيم المدين/الدائن لا يمكن أن تكون سالبة.',
                ]);
            }
        });
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
}
