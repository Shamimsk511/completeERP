<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Purchase extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'purchase_date',
        'invoice_no',
        'company_id',
        'godown_id',
        'total_amount',
        'labour_cost',
        'transportation_cost',
        'other_cost',
        'other_cost_description',
        'cost_distribution_method',
        'update_product_prices',
        'grand_total',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
        'labour_cost' => 'decimal:2',
        'transportation_cost' => 'decimal:2',
        'other_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'update_product_prices' => 'boolean',
    ];

    /**
     * Get total additional costs
     */
    public function getTotalAdditionalCostsAttribute()
    {
        return ($this->labour_cost ?? 0) + ($this->transportation_cost ?? 0) + ($this->other_cost ?? 0);
    }

    /**
     * Distribute additional costs to items based on method
     */
    public function distributeAdditionalCosts()
    {
        $totalAdditionalCost = $this->total_additional_costs;
        if ($totalAdditionalCost <= 0) {
            return;
        }

        $items = $this->items;
        if ($items->isEmpty()) {
            return;
        }

        $method = $this->cost_distribution_method ?? 'per_value';

        switch ($method) {
            case 'per_quantity':
                // Distribute based on quantity
                $totalQuantity = $items->sum('quantity');
                if ($totalQuantity > 0) {
                    $costPerUnit = $totalAdditionalCost / $totalQuantity;
                    foreach ($items as $item) {
                        $item->additional_cost = $costPerUnit * $item->quantity;
                        $item->effective_price = $item->purchase_price + $costPerUnit;
                        $item->save();
                    }
                }
                break;

            case 'equal':
                // Distribute equally among items
                $costPerItem = $totalAdditionalCost / $items->count();
                foreach ($items as $item) {
                    $item->additional_cost = $costPerItem;
                    $item->effective_price = $item->purchase_price + ($costPerItem / $item->quantity);
                    $item->save();
                }
                break;

            case 'per_value':
            default:
                // Distribute based on value (proportional to total_price)
                $totalValue = $items->sum('total_price');
                if ($totalValue > 0) {
                    foreach ($items as $item) {
                        $proportion = $item->total_price / $totalValue;
                        $item->additional_cost = $totalAdditionalCost * $proportion;
                        $item->effective_price = $item->purchase_price + ($item->additional_cost / $item->quantity);
                        $item->save();
                    }
                }
                break;
        }
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function godown()
    {
        return $this->belongsTo(Godown::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

}
