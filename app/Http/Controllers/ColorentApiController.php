<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colorent;

class ColorentApiController extends Controller
{
    public function index() 
    {
        return response()->json(Colorent::all());
    }

    public function update(Request $request, $id) 
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0'
        ]);

        $colorent = Colorent::findOrFail($id);
        $colorent->update($request->only(['stock', 'price']));
        
        return response()->json($colorent);
    }
}
