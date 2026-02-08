<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payee extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'type',
        'category',
        'company_id',
        'account_id',
        'opening_balance',
        'current_balance',
        'principal_amount',
        'principal_balance',
        'interest_rate',
        'interest_accrued',
        'interest_last_accrual_date',
        'loan_start_date',
        'loan_term_months',
        'installment_amount',
        'daily_kisti_amount',
        'daily_kisti_start_date',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'principal_balance' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'interest_accrued' => 'decimal:2',
        'interest_last_accrual_date' => 'date',
        'loan_start_date' => 'date',
        'daily_kisti_start_date' => 'date',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(PayableTransaction::class);
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Accounting\Account::class, 'account_id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    public function getLedgerBalanceAttribute(): float
    {
        if (array_key_exists('ledger_balance', $this->attributes) && $this->attributes['ledger_balance'] !== null) {
            return (float) $this->attributes['ledger_balance'];
        }

        return $this->payable_balance;
    }

    public function getPayableBalanceAttribute(): float
    {
        $opening = (float) ($this->opening_balance ?? 0);
        $cashIn = $this->attributes['cash_in_total'] ?? null;
        $cashOut = $this->attributes['cash_out_total'] ?? null;

        if ($cashIn !== null || $cashOut !== null) {
            $cashIn = (float) ($cashIn ?? 0);
            $cashOut = (float) ($cashOut ?? 0);
        } else {
            $cashIn = (float) $this->transactions()->where('transaction_type', 'cash_in')->sum('amount');
            $cashOut = (float) $this->transactions()->where('transaction_type', 'cash_out')->sum('amount');
        }

        $computed = $opening + $cashOut - $cashIn;

        if (abs($computed) < 0.0001 && abs($opening) < 0.0001 && abs($cashIn) < 0.0001 && abs($cashOut) < 0.0001) {
            $legacy = (float) ($this->current_balance ?? 0);
            if (abs($legacy) > 0.0001) {
                return $legacy;
            }
        }

        return $computed;
    }

    public function installments(): HasMany
    {
        return $this->hasMany(PayeeInstallment::class);
    }

    public function kistiSkips(): HasMany
    {
        return $this->hasMany(PayeeKistiSkip::class);
    }

    public function isLoanCategory(): bool
    {
        $category = $this->category ?: $this->type;
        return in_array($category, ['cc', 'sme', 'term_loan', 'daily_kisti'], true);
    }

    public function isCcLoan(): bool
    {
        return ($this->category ?: $this->type) === 'cc';
    }

    public function isSmeLoan(): bool
    {
        return ($this->category ?: $this->type) === 'sme';
    }

    public function isDailyKisti(): bool
    {
        return ($this->category ?: $this->type) === 'daily_kisti';
    }

    public function getDisplayCategoryAttribute(): string
    {
        $category = $this->category ?: $this->type ?: 'supplier';
        return ucwords(str_replace('_', ' ', $category));
    }
}
