<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;
use App\Models\TilesCategory;
use App\Models\TilesCalculationSetting;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Cache;

class DecorCalculatorController extends Controller
{
    public function getCategories()
    {
        $categories = TilesCategory::all();
        
        // Debug logging
        if ($categories->isEmpty()) {
            Log::info('No tiles categories found in database');
        } else {
            \Log::info('Categories found: ' . $categories->count());
        }
        
        return response()->json($categories);
    }
    
    public function getSettings($categoryId)
    {
        $settings = TilesCalculationSetting::where('tiles_category_id', $categoryId)->first();
        return response()->json($settings);
    }
    
 public function calculate(Request $request)
{
    $validated = $request->validate([
        'category_id' => 'required|exists:tiles_categories,id',
        'quantity' => 'required|numeric|min:0.01',   // Total area in square feet
        'height' => 'required|numeric|min:0.01',     // Wall height in feet
        'light_times' => 'required|numeric|min:0',
        'light_qty' => 'required|numeric|min:0',
        'deco_times' => 'required|numeric|min:0',
        'deco_qty' => 'required|numeric|min:0',
        'deep_times' => 'nullable|numeric|min:0',
        'deep_qty' => 'nullable|numeric|min:0',
        'exclude_deep' => 'boolean'
    ]);
    
    $category = TilesCategory::findOrFail($validated['category_id']);
    $tileHeight = (float) ($category->height ?? 0); // in inches
    $tileWidth = (float) ($category->width ?? 0);   // in inches

    if ($tileHeight <= 0 || $tileWidth <= 0) {
        return response()->json([
            'message' => 'Selected category is missing tile height/width.',
        ], 422);
    }
    
    // Convert wall height from feet to inches
    $wallHeightInches = $validated['height'] * 12;
    
    // Calculate how many tiles fit vertically (exact value with decimal)
    $totalVerticalTiles = $wallHeightInches / $tileHeight;
    $verticalDecimal = $totalVerticalTiles - floor($totalVerticalTiles);
    
    // Number of deco rows
    $decoRows = (float) $validated['deco_times'];
    if ($decoRows > $totalVerticalTiles) {
        return response()->json([
            'message' => 'Deco rows exceed total vertical tiles for the given height.',
        ], 422);
    }
    
    // Calculate remaining tiles after deco
    $remainingTiles = $totalVerticalTiles - $decoRows;
    
    // Distribute remaining tiles between light and deep
    $halfRemaining = $remainingTiles / 2;
    $halfDecimal = $halfRemaining - floor($halfRemaining);
    
    // Deep gets ceiling of half if decimal > 0.5, plus the vertical decimal
    if ($halfDecimal > 0.5) {
        $deepTiles = ceil($halfRemaining) + $verticalDecimal;
    } else {
        $deepTiles = floor($halfRemaining) + $verticalDecimal;
    }
    
    // Light gets the rest
    $lightTiles = $remainingTiles - $deepTiles;
    
    // If deep is excluded, allocate all to light
    if ($validated['exclude_deep']) {
        $lightTiles = $remainingTiles;
        $deepTiles = 0;
    }
    
    // Calculate wall width in feet
    $wallWidthFeet = $validated['quantity'] / $validated['height'];
    
    // Calculate number of tiles horizontally
    $wallWidthInches = $wallWidthFeet * 12;
    $horizontalTiles = ceil($wallWidthInches / $tileWidth);
    
    // Calculate total tiles for each type
    $totalDecoTiles = $horizontalTiles * $decoRows * $validated['deco_qty'];
    $deepQty = (float) ($validated['deep_qty'] ?? 0);
    if ($validated['exclude_deep']) {
        $deepQty = 0;
    }
    $totalDeepTiles = $horizontalTiles * $deepTiles * $deepQty;
    $totalLightTiles = $horizontalTiles * $lightTiles * $validated['light_qty'];
    
    // Calculate square footage for each type
    $tileAreaSqFt = ($tileHeight * $tileWidth) / 144; // Convert sq inches to sq feet
    $decoSqFt = $totalDecoTiles * $tileAreaSqFt;
    $deepSqFt = $totalDeepTiles * $tileAreaSqFt;
    $lightSqFt = $totalLightTiles * $tileAreaSqFt;
    
    return response()->json([
        'total_vertical_tiles' => $totalVerticalTiles,
        'deco_rows' => $decoRows,
        'light_rows' => $lightTiles,
        'deep_rows' => $deepTiles,
        'horizontal_tiles' => $horizontalTiles,
        'light_quantity' => $totalLightTiles,
        'light_sqft' => $lightSqFt,
        'deco_quantity' => $totalDecoTiles,
        'deco_sqft' => $decoSqFt,
        'deep_quantity' => $totalDeepTiles,
        'deep_sqft' => $deepSqFt,
        'total_tiles' => $totalLightTiles + $totalDecoTiles + $totalDeepTiles,
        'total_sqft' => $lightSqFt + $decoSqFt + $deepSqFt
    ]);
}


/**
 * Custom rounding function that rounds up if decimal part is >= 0.3
 *
 * @param float $value
 * @return int
 */
private function customRound($value)
{
    $decimal = $value - floor($value);
    if ($decimal >= 0.3) {
        return ceil($value);
    } else {
        return floor($value);
    }
}



    
    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'tiles_category_id' => 'required|exists:tiles_categories,id',
            'light_times' => 'required|numeric',
            'deco_times' => 'required|numeric',
            'deep_times' => 'required|numeric',
        ]);
        
        TilesCalculationSetting::updateOrCreate(
            ['tiles_category_id' => $validated['tiles_category_id']],
            $validated
        );
        
        return response()->json(['success' => true]);
    }
    
    public function getComponent()
    {
        return view('components.decor-calculator-modal');
    }

    public function index()
    {
        $tenantId = TenantContext::currentId();
        $cacheKey = 'decor_calculator_categories_' . ($tenantId ?? 'global');

        $categories = Cache::remember($cacheKey, 600, function () {
            return TilesCategory::with('calculationSettings')
                ->orderBy('name')
                ->get();
        });

        return view('admin.decor-calculator.index', compact('categories'));
    }

}
