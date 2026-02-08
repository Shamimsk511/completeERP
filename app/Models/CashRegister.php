<?php
// File: app/Models/CashRegister.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Exception;
use App\Models\Concerns\BelongsToTenant;

class CashRegister extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'opening_balance',
        'expected_closing_balance',
        'actual_closing_balance',
        'variance',
        'opened_at',
        'closed_at',
        'suspended_at',
        'opening_notes',
        'closing_notes',
        'status',
        'security_pin',
        'terminal',
    ];
    
    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'suspended_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'expected_closing_balance' => 'decimal:2',
        'actual_closing_balance' => 'decimal:2',
        'variance' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'security_pin',
    ];

    protected $attributes = [
        'status' => self::STATUS_OPEN,
        'terminal' => 'Terminal 1',
    ];

    // Status constants
    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cashRegister) {
            // Set default terminal if not provided
            if (!$cashRegister->terminal) {
                $cashRegister->terminal = 'Terminal 1';
            }
            
            // Set opened_at if not provided
            if (!$cashRegister->opened_at) {
                $cashRegister->opened_at = now();
            }
        });

        static::deleting(function ($cashRegister) {
            // Soft delete related transactions
            $cashRegister->transactions()->delete();
        });
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    /**
     * Get the user that owns this cash register
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get all transactions for this cash register
     */
    public function transactions()
    {
        return $this->hasMany(CashRegisterTransaction::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get user transactions only (excluding system transactions)
     */
    public function userTransactions()
    {
        return $this->hasMany(CashRegisterTransaction::class)
                    ->whereNotIn('transaction_type', [
                        'opening_balance',
                        'closing_balance',
                        'suspension',
                        'resumption',
                        'void'
                    ])
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get transactions by type
     */
    public function getTransactionsByType($type)
    {
        return $this->transactions()->where('transaction_type', $type);
    }

    /**
     * Check if register is currently open
     */
    public function isOpen()
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Check if register is closed
     */
    public function isClosed()
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /**
     * Check if register is suspended
     */
    public function isSuspended()
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Get the current session duration
     */
    public function getSessionDuration()
    {
        try {
            if ($this->closed_at) {
                return $this->opened_at->diffForHumans($this->closed_at, true);
            }
            
            return $this->opened_at->diffForHumans(null, true);
        } catch (Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get session duration in minutes
     */
    public function getSessionDurationInMinutes()
    {
        try {
            $endTime = $this->closed_at ?? now();
            return $this->opened_at->diffInMinutes($endTime);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Calculate total by transaction type
     */
    public function getTotalByType($type)
    {
        try {
            return $this->getTransactionsByType($type)->sum('amount') ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Calculate net cash flow (income - expenses)
     */
    public function getNetCashFlow()
    {
        try {
            $income = $this->getTotalByType('sale') + $this->getTotalByType('deposit');
            $expenses = $this->getTotalByType('return') + 
                       $this->getTotalByType('expense') + 
                       $this->getTotalByType('withdrawal');
            
            return $income - $expenses;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total transaction count (excluding system transactions)
     */
    public function getTotalTransactionCount()
    {
        try {
            return $this->userTransactions()->count();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get average transaction amount
     */
    public function getAverageTransactionAmount()
    {
        try {
            $count = $this->getTotalTransactionCount();
            if ($count === 0) {
                return 0;
            }

            $total = $this->userTransactions()->sum('amount');
            return $total / $count;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Check if register has variance
     */
    public function hasVariance()
    {
        return $this->variance && abs($this->variance) >= 0.01;
    }

    /**
     * Get variance type (surplus/shortage)
     */
    public function getVarianceType()
    {
        if (!$this->hasVariance()) {
            return 'balanced';
        }

        return $this->variance > 0 ? 'surplus' : 'shortage';
    }

    /**
     * Get formatted variance amount
     */
    public function getFormattedVariance()
    {
        if (!$this->hasVariance()) {
            return '৳0.00';
        }

        $prefix = $this->variance > 0 ? '+' : '';
        return $prefix . '৳' . number_format(abs($this->variance), 2);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        $classes = [
            self::STATUS_OPEN => 'badge-success',
            self::STATUS_CLOSED => 'badge-secondary',
            self::STATUS_SUSPENDED => 'badge-warning',
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    /**
     * Get status icon
     */
    public function getStatusIcon()
    {
        $icons = [
            self::STATUS_OPEN => 'fas fa-door-open',
            self::STATUS_CLOSED => 'fas fa-door-closed',
            self::STATUS_SUSPENDED => 'fas fa-pause',
        ];

        return $icons[$this->status] ?? 'fas fa-question';
    }

    /**
     * Check if register needs break (after 4+ hours)
     */
    public function needsBreak()
    {
        if (!$this->isOpen()) {
            return false;
        }

        try {
            $hoursWorked = $this->opened_at->diffInHours(now());
            return $hoursWorked >= 4;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if break is overdue (after 6+ hours)
     */
    public function isBreakOverdue()
    {
        if (!$this->isOpen()) {
            return false;
        }

        try {
            $hoursWorked = $this->opened_at->diffInHours(now());
            return $hoursWorked >= 6;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Calculate accuracy percentage for this register
     */
    public function getAccuracyPercentage()
    {
        if (!$this->isClosed() || !$this->actual_closing_balance) {
            return 100;
        }

        try {
            if ($this->expected_closing_balance == 0) {
                return $this->actual_closing_balance == 0 ? 100 : 0;
            }

            $accuracy = 100 - (abs($this->variance) / $this->expected_closing_balance * 100);
            return max(0, min(100, $accuracy));
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get performance metrics for this register
     */
    public function getPerformanceMetrics()
    {
        return [
            'session_duration' => $this->getSessionDurationInMinutes(),
            'total_transactions' => $this->getTotalTransactionCount(),
            'average_transaction' => $this->getAverageTransactionAmount(),
            'net_cash_flow' => $this->getNetCashFlow(),
            'accuracy_percentage' => $this->getAccuracyPercentage(),
            'variance_amount' => $this->variance ?? 0,
            'variance_type' => $this->getVarianceType(),
        ];
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter open registers
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope: Filter closed registers
     */
    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    /**
     * Scope: Filter suspended registers
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('opened_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter today's registers
     */
    public function scopeToday($query)
    {
        return $query->whereDate('opened_at', today());
    }

    /**
     * Scope: Filter with variance
     */
    public function scopeWithVariance($query)
    {
        return $query->whereNotNull('variance')
                    ->where(function($q) {
                        $q->where('variance', '>', 0.01)
                          ->orWhere('variance', '<', -0.01);
                    });
    }

    /**
     * Check if user can access this register
     */
    public function canBeAccessedBy($user)
    {
        // Owner can always access
        if ($this->user_id === $user->id) {
            return true;
        }

        // Check permissions
        return $user->can('cash-register-access-all');
    }

    /**
     * Check if register can be deleted
     */
    public function canBeDeleted()
    {
        // Only closed registers can be deleted
        return $this->status === self::STATUS_CLOSED;
    }
}
