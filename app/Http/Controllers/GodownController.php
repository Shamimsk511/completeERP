<?php

namespace App\Http\Controllers;

use App\Models\Godown;
use Illuminate\Http\Request;

class GodownController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:godown-list|godown-create|godown-edit|godown-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:godown-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:godown-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:godown-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $godowns = Godown::orderBy('name')->get();
        return view('godowns.index', compact('godowns'));
    }

    public function create()
    {
        return view('godowns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', $this->tenantUniqueRule('godowns', 'name')],
            'location' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            Godown::where('is_default', true)->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $validated['is_active'] = $request->has('is_active');

        $godown = Godown::create($validated);

        return redirect()->route('godowns.index')
            ->with('success', 'Godown created successfully.');
    }

    public function show(Godown $godown)
    {
        return view('godowns.show', compact('godown'));
    }

    public function edit(Godown $godown)
    {
        return view('godowns.edit', compact('godown'));
    }

    public function update(Request $request, Godown $godown)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', $this->tenantUniqueRule('godowns', 'name', $godown->id)],
            'location' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            Godown::where('is_default', true)->where('id', '!=', $godown->id)->update(['is_default' => false]);
            $validated['is_default'] = true;
        } else {
            $validated['is_default'] = false;
        }

        $validated['is_active'] = $request->has('is_active');

        $godown->update($validated);

        return redirect()->route('godowns.index')
            ->with('success', 'Godown updated successfully.');
    }

    public function destroy(Godown $godown)
    {
        if ($godown->is_default) {
            return redirect()->route('godowns.index')
                ->with('error', 'Default godown cannot be deleted.');
        }

        $godown->delete();

        return redirect()->route('godowns.index')
            ->with('success', 'Godown deleted successfully.');
    }
}
