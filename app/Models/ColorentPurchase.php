<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class ColorentPurchase extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'payee_id',
        'transaction_date',
        'reference_no',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function payee(): BelongsTo
    {
        return $this->belongsTo(Payee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ColorentPurchaseItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
