<?php

namespace App\Services;

use App\Models\Product;

class ProductStockService
{
    public static function handleStockUpdate(Product $product, array $data)
    {
        $isStockManaged = (bool) ($data['is_stock_managed'] ?? false);
        
        if (!$isStockManaged) {
            // Non-stock-managed products always have 0 stock
            $data['current_stock'] = 0;
            $data['opening_stock'] = 0; // Optional: reset opening stock too
        } else {
            // Stock-managed products: calculate based on opening stock changes
            if ($product->exists) {
                $openingStockDifference = ($data['opening_stock'] ?? 0) - $product->opening_stock;
                $data['current_stock'] = $product->current_stock + $openingStockDifference;
            } else {
                // New product: current stock = opening stock
                $data['current_stock'] = $data['opening_stock'] ?? 0;
            }
        }
        
        return $data;
    }
}
