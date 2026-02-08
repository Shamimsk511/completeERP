<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Godown;
use App\Models\ErpFeatureSetting;
use App\Services\ProductStockService;
use App\Services\GodownStockService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
{
    protected $results = [
        'success' => 0,
        'failures' => []
    ];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Skip empty rows
            if (empty($row['name'])) {
                continue;
            }
            
            try {
                $name = trim((string) $row['name']);
                $companyId = $row['company_id'] ?? null;
                $categoryId = $row['category_id'] ?? null;
                $purchasePrice = $row['purchase_price'] ?? null;
                $salePrice = $row['sale_price'] ?? null;

                // Check required fields
                if ($name === '' || $companyId === null || $categoryId === null || $purchasePrice === null || $salePrice === null) {
                    throw new \Exception('Missing required fields (name, company_id, category_id, purchase_price, sale_price).');
                }

                $openingStock = isset($row['opening_stock']) && $row['opening_stock'] !== '' ? (float) $row['opening_stock'] : 0;
                $isStockManaged = $this->parseBoolean($row['is_stock_managed'] ?? true);
                $defaultGodownId = isset($row['default_godown_id']) && $row['default_godown_id'] !== '' ? (int) $row['default_godown_id'] : null;

                $weightValue = isset($row['weight_value']) && $row['weight_value'] !== '' ? (float) $row['weight_value'] : null;
                $weightUnit = isset($row['weight_unit']) ? trim((string) $row['weight_unit']) : null;
                if ($weightUnit === '') {
                    $weightUnit = null;
                }

                $allowedWeightUnits = ['per_piece', 'per_box', 'per_unit'];
                if ($weightUnit !== null && !in_array($weightUnit, $allowedWeightUnits, true)) {
                    throw new \Exception('Invalid weight_unit. Allowed: per_piece, per_box, per_unit.');
                }

                $data = [
                    'name' => $name,
                    'description' => $row['description'] ?? null,
                    'company_id' => (int) $companyId,
                    'category_id' => (int) $categoryId,
                    'opening_stock' => $openingStock,
                    'purchase_price' => (float) $purchasePrice,
                    'sale_price' => (float) $salePrice,
                    'is_stock_managed' => $isStockManaged,
                    'weight_value' => $weightValue,
                    'weight_unit' => $weightUnit,
                ];

                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $data['default_godown_id'] = $defaultGodownId ?? Godown::defaultId();
                }

                $data = ProductStockService::handleStockUpdate(new Product(), $data);

                $product = Product::create($data);

                if (ErpFeatureSetting::isEnabled('godown_management')) {
                    $resolvedGodownId = $data['default_godown_id'] ?? Godown::defaultId();
                    if ($resolvedGodownId) {
                        $product->default_godown_id = $resolvedGodownId;
                        $product->save();
                        GodownStockService::setStock($product->id, $resolvedGodownId, (float) $product->current_stock);
                    }
                }
                
                $this->results['success']++;
            } catch (\Exception $e) {
                $this->results['failures'][] = [
                    'row_number' => $index + 2, // +2 for heading row and 0-index
                    'row_data' => json_encode($row),
                    'error' => $e->getMessage()
                ];
            }
        }
    }

    public function getResults()
    {
        return $this->results;
    }

    protected function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return true;
        }

        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return true;
        }

        return in_array($normalized, ['1', 'true', 'yes', 'y', 'managed'], true);
    }
}
