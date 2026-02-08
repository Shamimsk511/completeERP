<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class ChallanItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'challan_id',
        'invoice_item_id',
        'product_id',
        'godown_id',
        'description',
        'quantity',
        'boxes',
        'pieces',
    ];

    public function challan()
    {
        return $this->belongsTo(Challan::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function godown()
    {
        return $this->belongsTo(Godown::class);
    }
}
