<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'return_id',
        'type',
        'purpose',
        'method',
        'account_id',
        'amount',
        'discount_amount',
        'discount_reason',
        'note',
        'reference'
    ];

    /**
     * Get the cash/bank account used for this transaction
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Accounting\Account::class);
    }
    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function productReturn()
{
    return $this->belongsTo(ProductReturn::class, 'return_id');
}
// Add accessor for total amount including discount
    public function getTotalAmountAttribute()
    {
        return $this->amount + $this->discount_amount;
    }
    // Add accessor to check if transaction has discount
    public function getHasDiscountAttribute()
    {
        return ($this->discount_amount ?? 0) > 0;
    }
// Scope for payments (debits)
    public function scopePayments($query)
    {
        return $query->where('type', 'debit');
    }

    // Scope for invoices (credits)
    public function scopeInvoices($query)
    {
        return $query->where('type', 'credit');
    }

    // Get formatted amount
    public function getFormattedAmountAttribute(): string
    {
        return '৳' . number_format($this->amount, 2);
    }

}
