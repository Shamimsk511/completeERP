<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Colorent extends Model
{
    use BelongsToTenant;
    protected $fillable = ['name', 'stock', 'price'];
    
    protected $casts = [
        'stock' => 'integer',
        'price' => 'decimal:2'
    ];
}
