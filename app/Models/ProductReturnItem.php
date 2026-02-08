<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class ProductReturnItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'return_id',
        'product_id',
        'godown_id',
        'description',
        'quantity',
        'boxes',
        'pieces',
        'unit_price',
        'total',
        'invoice_item_id', // Can be null if not returning from a specific invoice
    ];

    public function productReturn()
    {
        return $this->belongsTo(ProductReturn::class, 'return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function godown()
    {
        return $this->belongsTo(Godown::class);
    }
}
