<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Accounting\Account;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'invoice_date',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid_amount',
        'due_amount',
        'previous_balance',      // Customer's balance BEFORE this invoice (snapshot)
        'initial_paid_amount',   // Payment made at invoice creation (not reallocated)
        'payment_method',
        'payment_status',
        'notes',
        'invoice_type',
        'sales_account_id',
        'delivery_status',
        'referrer_id',
        'referrer_compensated',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'referrer_compensated' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function referrer()
    {
        return $this->belongsTo(Referrer::class);
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function salesAccount()
    {
        return $this->belongsTo(Account::class, 'sales_account_id');
    }

    // public function transaction()
    // {
    //     return $this->hasOne(Transaction::class);
    // }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'invoice_id');
    }
    public function challans()
    {
        return $this->hasMany(Challan::class);
    }

    // Generate next invoice number
    public static function getNextInvoiceNumber()
    {
        $lastInvoice = self::withTrashed()->orderBy('id', 'desc')->first();
        $nextNumber = $lastInvoice ? (int)substr($lastInvoice->invoice_number, 3) + 1 : 1000;
        return 'INV' . $nextNumber;
    }

    public function hasPartialChallan()
{
    // Check if any invoice item has partial delivery
    foreach ($this->items as $item) {
        $deliveredQuantity = $item->getDeliveredQuantityAttribute();
        if ($deliveredQuantity > 0 && $deliveredQuantity < $item->quantity) {
            return true;
        }
    }
    return false;
}

/**
 * Calculate delivery status based on challans
 *
 * @return string
 */
public function calculateDeliveryStatus()
{
    $hasChallans = $this->challans()->exists();

    if (!$hasChallans) {
        return 'pending';
    }

    $allItemsDelivered = true;
    $anyItemDelivered = false;

    foreach ($this->items as $item) {
        $deliveredQuantity = $item->getDeliveredQuantityAttribute();

        if ($deliveredQuantity > 0) {
            $anyItemDelivered = true;
        }

        if ($deliveredQuantity < $item->quantity) {
            $allItemsDelivered = false;
        }
    }

    if ($allItemsDelivered) {
        return 'delivered';
    } else if ($anyItemDelivered) {
        return 'partial';
    } else {
        return 'pending';
    }
}

/**
 * Check if the invoice has any items remaining for delivery
 *
 * @return bool
 */
public function hasRemainingItems()
{
    foreach ($this->items as $item) {
        if ($item->getRemainingQuantityAttribute() > 0) {
            return true;
        }
    }
    return false;
}

public function hasActiveChallans()
{
    return $this->challans()->where('status', '!=', 'cancelled')->exists();
}
/**
 * Update the invoice's delivery status based on challan data
 *
 * @return void
 */
public function updateDeliveryStatus()
{
    $newStatus = $this->calculateDeliveryStatus();
    
    if ($this->delivery_status !== $newStatus) {
        $this->delivery_status = $newStatus;
        $this->save();
        
        return true;
    }
    
    return false;
}
public function getPaymentStatusColorAttribute()
{
    switch($this->payment_status) {
        case 'paid':
            return 'success';
        case 'partial':
            return 'info';
        case 'unpaid':
        default:
            return 'warning';
    }
}

public function getDeliveryStatusColorAttribute()
{
    switch($this->delivery_status) {
        case 'delivered':
            return 'success';
        case 'partial':
            return 'info';
        case 'pending':
        default:
            return 'warning';
    }
}

public function getTotalInWordsAttribute()
{
    // If using the custom helper:
    return \App\Helpers\NumberToWords::convert($this->total);
    
 
}
protected $withRelationshipAutoloading = true;
public function scopeWithBasicData($query)
    {
        // No longer needed - Laravel 12 auto-loads relationships
        // return $query->with(['customer', 'items.product']);
        return $query; // Laravel 12 handles this automatically
    }
}
