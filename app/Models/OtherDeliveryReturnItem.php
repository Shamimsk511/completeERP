<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class OtherDeliveryReturnItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'other_delivery_return_id',
        'product_id',
        'godown_id',
        'description',
        'quantity',
        'cartons',
        'pieces'
    ];

    public function otherDeliveryReturn()
    {
        return $this->belongsTo(OtherDeliveryReturn::class);
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
