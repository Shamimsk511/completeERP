<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;

class AccountEntry extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'account_id',
        'entry_date',
        'debit_amount',
        'credit_amount',
        'source_type',
        'source_id',
        'reference',
        'description',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
