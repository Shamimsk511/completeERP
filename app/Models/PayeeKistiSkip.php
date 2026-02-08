<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;

class PayeeKistiSkip extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'payee_id',
        'skip_date',
        'reason',
    ];

    protected $casts = [
        'skip_date' => 'date',
    ];

    public function payee(): BelongsTo
    {
        return $this->belongsTo(Payee::class);
    }
}
