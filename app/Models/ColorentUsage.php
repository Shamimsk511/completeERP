<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ColorentUsage extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'colorent_id',
        'used_at',
        'quantity',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'used_at' => 'date',
        'quantity' => 'integer',
    ];

    public function colorent(): BelongsTo
    {
        return $this->belongsTo(Colorent::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
