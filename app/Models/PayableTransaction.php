<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayableTransaction extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'payee_id',
        'transaction_type',
        'payment_method',
        'account_id',
        'reference_no',
        'amount',
        'principal_amount',
        'interest_amount',
        'kisti_days',
        'installment_id',
        'category',
        'description',
        'transaction_date',
        'skip_accounting',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'skip_accounting' => 'boolean',
    ];

    public function payee(): BelongsTo
    {
        return $this->belongsTo(Payee::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Accounting\Account::class, 'account_id');
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(PayeeInstallment::class, 'installment_id');
    }
}
