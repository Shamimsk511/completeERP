<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;

class AccountGroup extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'nature',
        'affects_gross_profit',
        'description',
        'is_system',
        'display_order',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the parent group
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class, 'parent_id');
    }

    /**
     * Get direct children groups
     */
    public function children(): HasMany
    {
        return $this->hasMany(AccountGroup::class, 'parent_id')->orderBy('display_order');
    }

    /**
     * Get all nested children (recursive)
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Get accounts directly under this group
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class)->orderBy('name');
    }

    /**
     * Get all accounts including those in child groups
     */
    public function getAllAccountsAttribute()
    {
        $accounts = $this->accounts;
        foreach ($this->children as $child) {
            $accounts = $accounts->merge($child->all_accounts);
        }
        return $accounts;
    }

    /**
     * Scope to get root groups (no parent)
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('display_order');
    }

    /**
     * Scope to filter by nature
     */
    public function scopeByNature($query, string $nature)
    {
        return $query->where('nature', $nature);
    }

    /**
     * Get the full hierarchical path
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Get the depth level in hierarchy
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /**
     * Check if this group can be deleted
     */
    public function canDelete(): bool
    {
        if ($this->is_system) {
            return false;
        }

        if ($this->accounts()->exists()) {
            return false;
        }

        if ($this->children()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Get the total balance of all accounts in this group (including child groups)
     * Returns array with 'balance' and 'balance_type' (debit/credit)
     */
    public function getGroupTotalAttribute(): array
    {
        $totalDebits = 0;
        $totalCredits = 0;

        // Sum balances of direct accounts
        foreach ($this->accounts as $account) {
            $balance = $account->running_balance;
            if ($balance['balance_type'] === 'debit') {
                $totalDebits += $balance['balance'];
            } else {
                $totalCredits += $balance['balance'];
            }
        }

        // Recursively sum balances of child groups
        foreach ($this->children as $child) {
            $childTotal = $child->group_total;
            if ($childTotal['balance_type'] === 'debit') {
                $totalDebits += $childTotal['balance'];
            } else {
                $totalCredits += $childTotal['balance'];
            }
        }

        $balance = abs($totalDebits - $totalCredits);
        $balanceType = $totalDebits >= $totalCredits ? 'debit' : 'credit';

        return [
            'balance' => $balance,
            'balance_type' => $balanceType,
            'total_debit' => $totalDebits,
            'total_credit' => $totalCredits,
        ];
    }

    /**
     * Get formatted group total for display
     */
    public function getFormattedGroupTotalAttribute(): string
    {
        $total = $this->group_total;
        if ($total['balance'] == 0) {
            return '৳0.00';
        }
        $symbol = $total['balance_type'] === 'debit' ? 'Dr' : 'Cr';
        return '৳' . number_format($total['balance'], 2) . ' ' . $symbol;
    }
}
