<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class ProductReturn extends Model
{
    use HasFactory, BelongsToTenant;
    
    protected $table = 'product_returns'; // Explicit table name for clarity

    protected $fillable = [
        'return_number',
        'customer_id',
        'invoice_id', // Can be null if not related to an invoice
        'return_date',
        'subtotal',
        'tax',
        'total',
        'payment_method',
        'status',
        'notes',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(ProductReturnItem::class, 'return_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'return_id');
    }

    // Generate next return number
    public static function getNextReturnNumber()
    {
        $lastReturn = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastReturn ? (int)substr($lastReturn->return_number, 3) + 1 : 1000;
        return 'RET' . $nextNumber;
    }
}
