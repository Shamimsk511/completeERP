<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'name',
        'description',
        'company_id',
        'category_id',
        'default_godown_id',
        'opening_stock',
        'current_stock',
        'purchase_price',
        'sale_price',
        'is_stock_managed',
        'weight_value',
        'weight_unit'
    ];
    protected $casts = [
        'is_stock_managed' => 'boolean',
        'opening_stock' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight_value' => 'decimal:3',
        'deleted_at' => 'datetime',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function defaultGodown()
    {
        return $this->belongsTo(Godown::class, 'default_godown_id');
    }

    public function godownStocks()
    {
        return $this->hasMany(ProductGodownStock::class);
    }

    /**
     * Get purchase items for this product
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get invoice items for this product (sales)
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get challan items for this product (deliveries)
     */
    public function challanItems()
    {
        return $this->hasMany(ChallanItem::class);
    }

    /**
     * Get product return items for this product
     */
    public function returnItems()
    {
        return $this->hasMany(ProductReturnItem::class);
    }

    /**
     * Get other delivery items for this product
     */
    public function otherDeliveryItems()
    {
        return $this->hasMany(OtherDeliveryItem::class);
    }

    /**
     * Get other delivery return items for this product
     */
    public function otherDeliveryReturnItems()
    {
        return $this->hasMany(OtherDeliveryReturnItem::class);
    }

    /**
     * Get total purchased quantity
     */
    public function getTotalPurchasedAttribute()
    {
        return $this->purchaseItems()->sum('quantity');
    }

    /**
     * Get total sold quantity (from invoices)
     */
    public function getTotalSoldAttribute()
    {
        return $this->invoiceItems()->sum('quantity');
    }

    /**
     * Get total delivered quantity (from challans)
     */
    public function getTotalDeliveredAttribute()
    {
        return $this->challanItems()->sum('quantity');
    }

    /**
     * Get total returned quantity
     */
    public function getTotalReturnedAttribute()
    {
        return $this->returnItems()->sum('quantity');
    }
}
