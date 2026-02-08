<?php
// File: app/Models/CashRegisterTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use App\Models\Concerns\BelongsToTenant;

class CashRegisterTransaction extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'cash_register_id',
        'transaction_id',
        'transaction_type',
        'payment_method',
        'amount',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Transaction types
    const TYPE_OPENING_BALANCE = 'opening_balance';
    const TYPE_CLOSING_BALANCE = 'closing_balance';
    const TYPE_SALE = 'sale';
    const TYPE_RETURN = 'return';
    const TYPE_EXPENSE = 'expense';
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_SUSPENSION = 'suspension';
    const TYPE_RESUMPTION = 'resumption';
    const TYPE_VOID = 'void';

    // Payment methods
    const METHOD_CASH = 'cash';
    const METHOD_BANK = 'bank';
    const METHOD_MOBILE_BANK = 'mobile_bank';
    const METHOD_CHEQUE = 'cheque';
    const METHOD_CARD = 'card';
    const METHOD_SYSTEM = 'system';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            // Set default values
            if (!$transaction->created_at) {
                $transaction->created_at = now();
            }
        });
    }

    /**
     * Get all available transaction types
     */
    public static function getTransactionTypes()
    {
        return [
            self::TYPE_SALE => 'Sale',
            self::TYPE_RETURN => 'Return',
            self::TYPE_EXPENSE => 'Expense',
            self::TYPE_DEPOSIT => 'Deposit',
            self::TYPE_WITHDRAWAL => 'Withdrawal',
        ];
    }

    /**
     * Get all available payment methods
     */
    public static function getPaymentMethods()
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_BANK => 'Bank Transfer',
            self::METHOD_MOBILE_BANK => 'Mobile Banking',
            self::METHOD_CHEQUE => 'Cheque',
            self::METHOD_CARD => 'Card Payment',
        ];
    }

    /**
     * Get the cash register that owns this transaction
     */
    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Get the related transaction (if linked to main transactions table)
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the user who created this transaction
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this transaction affects the cash balance positively
     */
    public function isIncomeTransaction()
    {
        return in_array($this->transaction_type, [
            self::TYPE_SALE,
            self::TYPE_DEPOSIT,
            self::TYPE_OPENING_BALANCE,
        ]);
    }

    /**
     * Check if this transaction affects the cash balance negatively
     */
    public function isExpenseTransaction()
    {
        return in_array($this->transaction_type, [
            self::TYPE_RETURN,
            self::TYPE_EXPENSE,
            self::TYPE_WITHDRAWAL,
        ]);
    }

    /**
     * Check if this transaction is a system transaction
     */
    public function isSystemTransaction()
    {
        return in_array($this->transaction_type, [
            self::TYPE_OPENING_BALANCE,
            self::TYPE_CLOSING_BALANCE,
            self::TYPE_SUSPENSION,
            self::TYPE_RESUMPTION,
            self::TYPE_VOID,
        ]);
    }

    /**
     * Check if this transaction can be voided
     */
    public function canBeVoided()
    {
        // Cannot void system transactions or already voided transactions
        if ($this->isSystemTransaction()) {
            return false;
        }

        // Check if already voided
        if (strpos($this->notes ?? '', '[VOIDED]') !== false) {
            return false;
        }

        // Can only void transactions from open registers
        try {
            return $this->cashRegister && $this->cashRegister->status === 'open';
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute()
    {
        return '৳' . number_format($this->amount, 2);
    }

    /**
     * Get transaction type icon
     */
    public function getTypeIconAttribute()
    {
        $icons = [
            self::TYPE_SALE => 'fas fa-shopping-cart',
            self::TYPE_RETURN => 'fas fa-undo',
            self::TYPE_EXPENSE => 'fas fa-minus',
            self::TYPE_DEPOSIT => 'fas fa-plus',
            self::TYPE_WITHDRAWAL => 'fas fa-arrow-up',
            self::TYPE_OPENING_BALANCE => 'fas fa-door-open',
            self::TYPE_CLOSING_BALANCE => 'fas fa-door-closed',
            self::TYPE_SUSPENSION => 'fas fa-pause',
            self::TYPE_RESUMPTION => 'fas fa-play',
            self::TYPE_VOID => 'fas fa-ban',
        ];

        return $icons[$this->transaction_type] ?? 'fas fa-question';
    }

    /**
     * Get transaction type color class
     */
    public function getTypeColorAttribute()
    {
        $colors = [
            self::TYPE_SALE => 'success',
            self::TYPE_RETURN => 'danger',
            self::TYPE_EXPENSE => 'warning',
            self::TYPE_DEPOSIT => 'info',
            self::TYPE_WITHDRAWAL => 'secondary',
            self::TYPE_OPENING_BALANCE => 'primary',
            self::TYPE_CLOSING_BALANCE => 'dark',
            self::TYPE_SUSPENSION => 'warning',
            self::TYPE_RESUMPTION => 'success',
            self::TYPE_VOID => 'danger',
        ];

        return $colors[$this->transaction_type] ?? 'secondary';
    }

    /**
     * Get payment method icon
     */
    public function getMethodIconAttribute()
    {
        $icons = [
            self::METHOD_CASH => 'fas fa-money-bill-wave',
            self::METHOD_BANK => 'fas fa-university',
            self::METHOD_MOBILE_BANK => 'fas fa-mobile-alt',
            self::METHOD_CHEQUE => 'fas fa-file-invoice',
            self::METHOD_CARD => 'fas fa-credit-card',
            self::METHOD_SYSTEM => 'fas fa-cog',
        ];

        return $icons[$this->payment_method] ?? 'fas fa-question';
    }

    /**
     * Get transaction display name for dropdowns
     */
    public function getDisplayNameAttribute()
    {
        try {
            $parts = [];
            $parts[] = "#{$this->id}";
            $parts[] = ucfirst(str_replace('_', ' ', $this->transaction_type));
            $parts[] = $this->formatted_amount;
            $parts[] = "(" . $this->created_at->format('d M Y, h:i A') . ")";
            
            if ($this->notes && !$this->isSystemTransaction()) {
                $parts[] = "- " . \Illuminate\Support\Str::limit($this->notes, 30);
            }
            
            return implode(' ', $parts);
        } catch (Exception $e) {
            return "Transaction #{$this->id}";
        }
    }

    /**
     * Scope: Filter by transaction type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope: Filter by payment method
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope: Filter income transactions
     */
    public function scopeIncome($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_SALE,
            self::TYPE_DEPOSIT,
            self::TYPE_OPENING_BALANCE,
        ]);
    }

    /**
     * Scope: Filter expense transactions
     */
    public function scopeExpense($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_RETURN,
            self::TYPE_EXPENSE,
            self::TYPE_WITHDRAWAL,
        ]);
    }

    /**
     * Scope: Filter non-system transactions
     */
    public function scopeUserTransactions($query)
    {
        return $query->whereNotIn('transaction_type', [
            self::TYPE_OPENING_BALANCE,
            self::TYPE_CLOSING_BALANCE,
            self::TYPE_SUSPENSION,
            self::TYPE_RESUMPTION,
            self::TYPE_VOID,
        ]);
    }

    /**
     * Scope: Filter today's transactions
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Filter transactions by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter non-voided transactions
     */
    public function scopeNotVoided($query)
    {
        return $query->where(function($q) {
            $q->whereNull('notes')
              ->orWhere('notes', 'not like', '%[VOIDED]%');
        });
    }

    /**
     * Scope: Filter voided transactions
     */
    public function scopeVoided($query)
    {
        return $query->where('notes', 'like', '%[VOIDED]%');
    }

    /**
     * Check if transaction is voided
     */
    public function isVoided()
    {
        return strpos($this->notes ?? '', '[VOIDED]') !== false;
    }

    /**
     * Get the impact of this transaction on register balance
     */
    public function getBalanceImpact()
    {
        if ($this->isIncomeTransaction()) {
            return $this->amount;
        } elseif ($this->isExpenseTransaction()) {
            return -$this->amount;
        }
        
        return 0; // System transactions don't affect balance
    }

    /**
     * Get human-readable transaction description
     */
    public function getDescriptionAttribute()
    {
        $typeNames = [
            self::TYPE_SALE => 'Sale Transaction',
            self::TYPE_RETURN => 'Return Transaction',
            self::TYPE_EXPENSE => 'Expense Transaction',
            self::TYPE_DEPOSIT => 'Cash Deposit',
            self::TYPE_WITHDRAWAL => 'Cash Withdrawal',
            self::TYPE_OPENING_BALANCE => 'Opening Balance',
            self::TYPE_CLOSING_BALANCE => 'Closing Balance',
            self::TYPE_SUSPENSION => 'Register Suspension',
            self::TYPE_RESUMPTION => 'Register Resumption',
            self::TYPE_VOID => 'Voided Transaction',
        ];

        return $typeNames[$this->transaction_type] ?? 'Unknown Transaction';
    }
}
