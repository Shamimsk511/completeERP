<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class PurchaseItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'godown_id',
        'quantity',
        'purchase_price',
        'total_price',
        'additional_cost',
        'effective_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'additional_cost' => 'decimal:2',
        'effective_price' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function godown()
    {
        return $this->belongsTo(Godown::class);
    }

    /**
     * Get total cost including additional costs
     */
    public function getTotalWithAdditionalCostAttribute()
    {
        return $this->total_price + ($this->additional_cost ?? 0);
    }
}
