<?php

namespace App\Console\Commands;

use App\Models\Godown;
use App\Models\Product;
use App\Models\ProductGodownStock;
use Illuminate\Console\Command;

class AssignDefaultGodownToProducts extends Command
{
    protected $signature = 'products:assign-default-godown';

    protected $description = 'Assign all products to the default godown and seed stock if missing.';

    public function handle(): int
    {
        $default = Godown::where('is_default', true)->first();

        if (!$default) {
            $default = Godown::create([
                'name' => 'Default Godown',
                'location' => null,
                'is_default' => true,
                'is_active' => true,
            ]);
            $this->info('Default godown created.');
        }

        $updatedDefaults = 0;
        $stockSeeded = 0;
        $stockCreated = 0;

        $products = Product::all();

        foreach ($products as $product) {
            if (!$product->default_godown_id) {
                $product->default_godown_id = $default->id;
                $product->save();
                $updatedDefaults++;
            }

            $hasStockRows = ProductGodownStock::where('product_id', $product->id)->exists();

            if (!$hasStockRows) {
                ProductGodownStock::updateOrCreate(
                    ['product_id' => $product->id, 'godown_id' => $default->id],
                    ['quantity' => $product->current_stock]
                );
                $stockSeeded++;
            } else {
                $created = ProductGodownStock::firstOrCreate(
                    ['product_id' => $product->id, 'godown_id' => $default->id],
                    ['quantity' => 0]
                );
                if ($created->wasRecentlyCreated) {
                    $stockCreated++;
                }
            }
        }

        $this->info("Default godown assigned to {$updatedDefaults} product(s).");
        $this->info("Seeded stock rows for {$stockSeeded} product(s) with no godown stock.");
        $this->info("Created empty default stock rows for {$stockCreated} product(s).");

        return self::SUCCESS;
    }
}
