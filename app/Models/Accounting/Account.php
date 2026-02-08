<?php

namespace App\Models\Accounting;

use App\Models\Customer;
use App\Models\Payee;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;

class Account extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'name',
        'code',
        'account_group_id',
        'account_type',
        'opening_balance',
        'opening_balance_type',
        'current_balance',
        'current_balance_type',
        'linkable_type',
        'linkable_id',
        'bank_name',
        'bank_account_number',
        'ifsc_code',
        'is_active',
        'is_system',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get the account group
     */
    public function accountGroup(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class);
    }

    /**
     * Get all ledger entries for this account
     */
    public function accountEntries(): HasMany
    {
        return $this->hasMany(AccountEntry::class);
    }

    /**
     * Get the linked entity (customer, payee, or company)
     */
    public function getLinkedEntityAttribute()
    {
        if (!$this->linkable_type || !$this->linkable_id) {
            return null;
        }

        return match ($this->linkable_type) {
            'customer' => Customer::find($this->linkable_id),
            'payee' => Payee::find($this->linkable_id),
            'company' => Company::find($this->linkable_id),
            'employee' => Employee::find($this->linkable_id),
            default => null,
        };
    }

    /**
     * Calculate running balance from ledger entries
     */
    public function getRunningBalanceAttribute(): array
    {
        $debits = $this->accountEntries()->sum('debit_amount');
        $credits = $this->accountEntries()->sum('credit_amount');

        $openingDebit = $this->opening_balance_type === 'debit' ? $this->opening_balance : 0;
        $openingCredit = $this->opening_balance_type === 'credit' ? $this->opening_balance : 0;

        $totalDebits = $openingDebit + $debits;
        $totalCredits = $openingCredit + $credits;

        return [
            'debit' => $totalDebits,
            'credit' => $totalCredits,
            'balance' => abs($totalDebits - $totalCredits),
            'balance_type' => $totalDebits >= $totalCredits ? 'debit' : 'credit',
        ];
    }

    /**
     * Get formatted balance for display
     */
    public function getFormattedBalanceAttribute(): string
    {
        $balance = $this->running_balance;
        $symbol = $balance['balance_type'] === 'debit' ? 'Dr' : 'Cr';
        return '৳' . number_format($balance['balance'], 2) . ' ' . $symbol;
    }

    /**
     * Scope to get active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by account type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Scope to get cash accounts
     */
    public function scopeCashAccounts($query)
    {
        return $query->where('account_type', 'cash');
    }

    /**
     * Scope to get bank accounts
     */
    public function scopeBankAccounts($query)
    {
        return $query->where('account_type', 'bank');
    }

    /**
     * Scope to get customer accounts
     */
    public function scopeCustomerAccounts($query)
    {
        return $query->where('account_type', 'customer');
    }

    /**
     * Scope to get supplier accounts
     */
    public function scopeSupplierAccounts($query)
    {
        return $query->where('account_type', 'supplier');
    }

    /**
     * Check if account can be deleted
     */
    public function canDelete(): bool
    {
        if ($this->is_system) {
            return false;
        }

        if ($this->accountEntries()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Get account nature from group
     */
    public function getNatureAttribute(): string
    {
        return $this->accountGroup?->nature ?? 'assets';
    }
}
