<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TilesCategory;
use App\Models\TilesCalculationSetting;
use Illuminate\Http\Request;

class TilesCategoryController extends Controller
{
    public function index()
    {
        $categories = TilesCategory::latest()->get();
        return view('admin.tiles-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.tiles-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'height' => 'nullable|numeric',
            'width' => 'nullable|numeric',
        ]);

        TilesCategory::create($validated);

        return redirect()->route('admin.tiles-categories.index')
            ->with('success', 'Tiles category created successfully');
    }

    public function edit(TilesCategory $tilesCategory)
    {
        $settings = $tilesCategory->calculationSettings;
        return view('admin.tiles-categories.edit', [
            'category' => $tilesCategory,
            'settings' => $settings
        ]);
    }

    public function update(Request $request, TilesCategory $tilesCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'height' => 'nullable|numeric',
            'width' => 'nullable|numeric',
        ]);

        $tilesCategory->update($validated);

        return redirect()->route('admin.tiles-categories.index')
            ->with('success', 'Tiles category updated successfully');
    }

    public function destroy(TilesCategory $tilesCategory)
    {
        $tilesCategory->delete();

        return redirect()->route('admin.tiles-categories.index')
            ->with('success', 'Tiles category deleted successfully');
    }
    
    public function saveSettings(Request $request, TilesCategory $tilesCategory)
    {
        $validated = $request->validate([
            'light_times' => 'required|numeric',
            'deco_times' => 'required|numeric',
            'deep_times' => 'required|numeric',
        ]);
        
        TilesCalculationSetting::updateOrCreate(
            ['tiles_category_id' => $tilesCategory->id],
            $validated
        );
        
        return redirect()->route('admin.tiles-categories.edit', $tilesCategory)
            ->with('success', 'Calculation settings saved successfully');
    }
}
