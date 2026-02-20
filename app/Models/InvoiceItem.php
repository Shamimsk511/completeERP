<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class InvoiceItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'code',
        'quantity',
        'boxes',
        'pieces',
        'unit_price',
        'total',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function challanItems()
    {
        return $this->hasMany(ChallanItem::class);
    }

    public function getRemainingQuantityAttribute()
    {
        $deliveredQuantity = ChallanItem::where('invoice_item_id', $this->id)
            ->whereHas('challan')
            ->sum('quantity');
        return $this->quantity - $deliveredQuantity;
    }

        // Get delivered quantity
    public function getDeliveredQuantityAttribute()
        {
            return ChallanItem::where('invoice_item_id', $this->id)
                ->whereHas('challan')
                ->sum('quantity');
        }


/**
 * Get the delivered quantity via valid challans
 * 
 * @return float
 */
public function getDeliveredQuantityViaChallans()
{
    return ChallanItem::whereHas('challan', function($query) {
            $query->where('invoice_id', $this->invoice_id)
                  ->where('status', '!=', 'cancelled');
        })
        ->where('product_id', $this->product_id)
        ->sum('quantity');
}



}
