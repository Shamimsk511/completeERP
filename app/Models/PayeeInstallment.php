<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;

class PayeeInstallment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'payee_id',
        'installment_number',
        'due_date',
        'principal_due',
        'interest_due',
        'total_due',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'date',
        'principal_due' => 'decimal:2',
        'interest_due' => 'decimal:2',
        'total_due' => 'decimal:2',
    ];

    public function payee(): BelongsTo
    {
        return $this->belongsTo(Payee::class);
    }
}
