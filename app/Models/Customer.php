<?php

namespace App\Models;


use Illuminate\Support\Facades\Schema; // Ensure Schema facade is imported
use App\Models\CallLog;
use App\Models\Challan;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\ProductReturn;
use App\Models\DebtCollectionTracking;
use App\Models\Accounting\Account;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Accounting\AccountGroup;

class Customer extends Model implements Authenticatable
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'opening_balance',
        'outstanding_balance',
        'account_group_id',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'password_change_skipped_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ===== AUTHENTICATABLE INTERFACE IMPLEMENTATION =====
    
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->password ?: $this->phone;
    }

    public function getRememberToken()
    {
        return $this->remember_token ?? null;
    }

    public function setRememberToken($value)
    {
        // Only set if column exists
        if (Schema::hasColumn('customers', 'remember_token')) {
            $this->remember_token = $value;
        }
    }

    public function getRememberTokenName()
    {
        return \Schema::hasColumn('customers', 'remember_token') ? 'remember_token' : null;
    }

    public function getAuthPasswordName()
    {
        return 'phone';
    }

    // ===== CUSTOM AUTHENTICATION METHODS =====

    // Generate username (firstname + customer ID)
    public function getUsernameAttribute(): string
    {
        $firstName = explode(' ', trim($this->name))[0];
        return strtolower($firstName) . $this->id;
    }

    // Check if customer can login
    public function getCanLoginAttribute(): bool
    {
        return !empty($this->phone);
    }

    // ===== RELATIONSHIPS =====
    
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function challans(): HasManyThrough
    {
        return $this->hasManyThrough(
            Challan::class,
            Invoice::class,
            'customer_id',
            'invoice_id',
            'id',
            'id'
        );
    }

    public function productReturns(): HasMany
    {
        return $this->hasMany(ProductReturn::class);
    }

    public function debtCollectionTracking(): HasOne
    {
        return $this->hasOne(DebtCollectionTracking::class);
    }

    public function lastTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class)->latestOfMany();
    }

    public function lastPayment(): HasOne
    {
        return $this->hasOne(Transaction::class)
            ->where('type', 'debit')
            ->latestOfMany();
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    /**
     * Get the ledger account for this customer (Sundry Debtor)
     */
    public function ledgerAccount(): HasOne
    {
        return $this->hasOne(Account::class, 'linkable_id')
            ->where('linkable_type', 'customer');
    }

    /**
     * Get the account group (customer group) for this customer
     */
    public function accountGroup(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class, 'account_group_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get ledger balance from the accounting system
     */
    public function getLedgerBalanceAttribute(): ?array
    {
        $account = $this->ledgerAccount;
        return $account ? $account->running_balance : null;
    }

    // ===== SCOPES AND ACCESSORS =====
    
    public function scopeWithOutstanding($query)
    {
        return $query->where('outstanding_balance', '>', 0);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereHas('debtCollectionTracking', function ($q) {
            $q->where('priority', 'high');
        });
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->debtCollectionTracking || !$this->debtCollectionTracking->due_date) {
            return null;
        }
        return max(0, now()->diffInDays($this->debtCollectionTracking->due_date, false));
    }

    public function getFormattedBalanceAttribute(): string
    {
        return '৳' . number_format($this->outstanding_balance, 2);
    }

    public function getPortalStatsAttribute(): array
    {
        return [
            'total_invoices' => $this->invoices()->count(),
            'total_transactions' => $this->transactions()->count(),
            'total_returns' => $this->productReturns()->count(),
            'total_challans' => $this->challans()->count(),
            'last_transaction_date' => optional($this->lastTransaction())->created_at?->format('M d, Y'),
            'total_paid' => (float) $this->transactions()
                ->where('type', 'debit')
                ->selectRaw('COALESCE(SUM(amount + COALESCE(discount_amount, 0)), 0) as total')
                ->value('total'),
            'total_charged' => $this->transactions()->where('type', 'credit')->sum('amount'),
        ];
    }

    // ===== SEARCH METHODS =====
    
    public static function searchByUsername($username, $limit = 5)
    {
        $username = trim(strtolower($username));
        
        if (empty($username) || strlen($username) < 2) {
            return collect();
        }

        if (is_numeric($username)) {
            $customer = self::find((int) $username);
            return ($customer && $customer->can_login) ? collect([$customer]) : collect();
        }

        return self::where(function($query) use ($username) {
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$username}%"])
                  ->orWhereRaw('LOWER(SUBSTRING_INDEX(name, " ", 1)) LIKE ?', ["%{$username}%"]);
        })
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->take($limit)
        ->get();
    }

}
