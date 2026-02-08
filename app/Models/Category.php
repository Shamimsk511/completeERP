<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Category extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'is_simple_product',
        'tile_width_in',
        'tile_length_in',
        'box_pcs',
        'pieces_feet',
        'weight_value',
        'weight_unit'
    ];

    protected $casts = [
        'is_simple_product' => 'boolean',
        'tile_width_in' => 'decimal:2',
        'tile_length_in' => 'decimal:2',
        'pieces_feet' => 'decimal:4',
        'weight_value' => 'decimal:3',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
