<?php

namespace App\Models;

use App\Models\TilesCalculationSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToTenant;

class TilesCategory extends Model
{
    use HasFactory, BelongsToTenant;
    
    protected $fillable = ['name', 'height', 'width'];
    
    public function calculationSettings()
    {
        return $this->hasOne(TilesCalculationSetting::class);
    }
    
    // Extract height and width from name if not provided
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (!$category->height || !$category->width) {
                preg_match('/(\d+)x(\d+)/i', $category->name, $matches);
                if (count($matches) == 3) {
                    $category->height = $matches[1];
                    $category->width = $matches[2];
                }
            }
        });
    }
}
