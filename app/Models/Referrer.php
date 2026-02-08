<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Referrer extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'phone',
        'profession',
        'note',
        'compensation_enabled',
        'gift_enabled',
    ];

    protected $casts = [
        'compensation_enabled' => 'boolean',
        'gift_enabled' => 'boolean',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
