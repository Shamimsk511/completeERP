<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColorentPurchaseItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'colorent_purchase_id',
        'colorent_id',
        'quantity',
        'unit_cost',
        'line_total',
        'update_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
        'update_price' => 'boolean',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(ColorentPurchase::class, 'colorent_purchase_id');
    }

    public function colorent(): BelongsTo
    {
        return $this->belongsTo(Colorent::class);
    }
}
