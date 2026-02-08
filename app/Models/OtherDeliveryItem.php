<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class OtherDeliveryItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'other_delivery_id',
        'product_id',
        'godown_id',
        'description',
        'quantity',
        'cartons',
        'pieces'
    ];

    public function otherDelivery()
    {
        return $this->belongsTo(OtherDelivery::class);
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
