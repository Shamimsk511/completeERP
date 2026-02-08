<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToTenant;

class TilesCalculationSetting extends Model
{
    use HasFactory, BelongsToTenant;
    
    protected $fillable = [
        'tiles_category_id',
        'light_times',
        'deco_times',
        'deep_times'
    ];
    
    public function category()
    {
        return $this->belongsTo(TilesCategory::class, 'tiles_category_id');
    }
}
