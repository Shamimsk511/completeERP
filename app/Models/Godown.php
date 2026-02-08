<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Godown extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'location',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function stocks()
    {
        return $this->hasMany(ProductGodownStock::class);
    }

    public static function default(): ?self
    {
        return self::where('is_default', true)->first();
    }

    public static function defaultId(): ?int
    {
        return optional(self::default())->id;
    }
}
