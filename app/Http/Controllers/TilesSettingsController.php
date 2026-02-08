<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TilesCategory;
use App\Models\TilesCalculationSetting;
use Illuminate\Http\Request;

class TilesSettingController extends Controller
{
    public function index()
    {
        $settings = TilesCalculationSetting::with('category')->latest()->get();
        $categories = TilesCategory::all();
        return view('admin.tiles-settings.index', compact('settings', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tiles_category_id' => 'required|exists:tiles_categories,id',
            'light_times' => 'required|numeric',
            'deco_times' => 'required|numeric',
            'deep_times' => 'required|numeric',
        ]);
        
        // Check if settings already exist for this category
        $existing = TilesCalculationSetting::where('tiles_category_id', $validated['tiles_category_id'])->first();
        
        if ($existing) {
            return redirect()->route('admin.tiles-settings.index')
                ->with('error', 'Settings for this category already exist. Please edit the existing settings.');
        }
        
        TilesCalculationSetting::create($validated);
        
        return redirect()->route('admin.tiles-settings.index')
            ->with('success', 'Calculation settings created successfully');
    }

    public function update(Request $request, TilesCalculationSetting $tilesSetting)
    {
        $validated = $request->validate([
            'tiles_category_id' => 'required|exists:tiles_categories,id',
            'light_times' => 'required|numeric',
            'deco_times' => 'required|numeric',
            'deep_times' => 'required|numeric',
        ]);
        
        // Check if we're trying to change the category to one that already has settings
        if ($tilesSetting->tiles_category_id != $validated['tiles_category_id']) {
            $existing = TilesCalculationSetting::where('tiles_category_id', $validated['tiles_category_id'])->first();
            
            if ($existing && $existing->id != $tilesSetting->id) {
                return redirect()->route('admin.tiles-settings.index')
                    ->with('error', 'Settings for this category already exist. Please edit the existing settings.');
            }
        }
        
        $tilesSetting->update($validated);
        
        return redirect()->route('admin.tiles-settings.index')
            ->with('success', 'Calculation settings updated successfully');
    }

    public function destroy(TilesCalculationSetting $tilesSetting)
    {
        $tilesSetting->delete();
        
        return redirect()->route('admin.tiles-settings.index')
            ->with('success', 'Calculation settings deleted successfully');
    }
}
