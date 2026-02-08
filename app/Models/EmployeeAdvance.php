<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToTenant;

class EmployeeAdvance extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'employee_id',
        'amount',
        'date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function deductions()
    {
        return $this->hasMany(EmployeeAdvanceDeduction::class);
    }

    public function getDeductedAmountAttribute()
    {
        return $this->deductions()->sum('amount');
    }

    public function getOutstandingAmountAttribute()
    {
        return max(0, $this->amount - $this->deducted_amount);
    }
}
