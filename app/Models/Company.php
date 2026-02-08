<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Accounting\Account;
use App\Models\Concerns\BelongsToTenant;

class Company extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'description',
        'contact',
        'type',
        'opening_balance',
        'opening_balance_type',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function isSupplierType(): bool
    {
        $type = $this->type ?: 'both';
        return in_array($type, ['supplier', 'both'], true);
    }

    public function isBrandType(): bool
    {
        $type = $this->type ?: 'both';
        return in_array($type, ['brand', 'both'], true);
    }

    public function scopeSuppliers($query)
    {
        return $query->whereIn('type', ['supplier', 'both'])->orWhereNull('type');
    }

    public function scopeBrands($query)
    {
        return $query->whereIn('type', ['brand', 'both'])->orWhereNull('type');
    }

    /**
     * Get the ledger account for this company/vendor (Sundry Creditor)
     */
    public function ledgerAccount(): HasOne
    {
        return $this->hasOne(Account::class, 'linkable_id')
            ->where('linkable_type', 'company');
    }

    /**
     * Get ledger balance from the accounting system
     */
    public function getLedgerBalanceAttribute(): ?array
    {
        $account = $this->ledgerAccount;
        return $account ? $account->running_balance : null;
    }
}
