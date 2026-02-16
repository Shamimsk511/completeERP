<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $categories = Category::all();
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', $this->tenantUniqueRule('categories', 'name')],
            'is_simple_product' => 'nullable|boolean',
            'box_pcs' => 'nullable|integer|min:0',
            'tile_width_in' => 'nullable|numeric|min:0',
            'tile_length_in' => 'nullable|numeric|min:0',
            'weight_value' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|in:per_piece,per_box,per_unit',
        ]);

        $isSimple = $request->boolean('is_simple_product');

        if (!$isSimple) {
            $request->validate([
                'box_pcs' => 'required|integer|min:1',
                'tile_width_in' => 'required|numeric|min:0.01',
                'tile_length_in' => 'required|numeric|min:0.01',
            ]);
        }

        $data = $request->only([
            'name',
            'is_simple_product',
            'box_pcs',
            'tile_width_in',
            'tile_length_in',
            'weight_value',
            'weight_unit',
        ]);

        if ($isSimple) {
            $data['box_pcs'] = 0;
            $data['tile_width_in'] = null;
            $data['tile_length_in'] = null;
            $data['pieces_feet'] = 0;
        } else {
            $width = (float) $data['tile_width_in'];
            $length = (float) $data['tile_length_in'];
            $data['pieces_feet'] = $width > 0 && $length > 0
                ? round(($width * $length) / 144, 4)
                : 0;
        }

        $category = Category::create($data);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($category);
        }

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', $this->tenantUniqueRule('categories', 'name', $category->id)],
            'is_simple_product' => 'nullable|boolean',
            'box_pcs' => 'nullable|integer|min:0',
            'tile_width_in' => 'nullable|numeric|min:0',
            'tile_length_in' => 'nullable|numeric|min:0',
            'weight_value' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|in:per_piece,per_box,per_unit',
        ]);

        $isSimple = $request->boolean('is_simple_product');

        if (!$isSimple) {
            $request->validate([
                'box_pcs' => 'required|integer|min:1',
                'tile_width_in' => 'required|numeric|min:0.01',
                'tile_length_in' => 'required|numeric|min:0.01',
            ]);
        }

        $data = $request->only([
            'name',
            'is_simple_product',
            'box_pcs',
            'tile_width_in',
            'tile_length_in',
            'weight_value',
            'weight_unit',
        ]);

        if ($isSimple) {
            $data['box_pcs'] = 0;
            $data['tile_width_in'] = null;
            $data['tile_length_in'] = null;
            $data['pieces_feet'] = 0;
        } else {
            $width = (float) $data['tile_width_in'];
            $length = (float) $data['tile_length_in'];
            $data['pieces_feet'] = $width > 0 && $length > 0
                ? round(($width * $length) / 144, 4)
                : 0;
        }

        $category->update($data);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully');
    }

    /**
     * Remove the specified category from storage.
     */
   public function destroy(Category $category)
{
    $category->delete();
    
    if(request()->ajax()) {
        return response()->json(['success' => true]);
    }
    
    return redirect()->route('categories.index')
        ->with('success', 'Category deleted successfully');
}

}
