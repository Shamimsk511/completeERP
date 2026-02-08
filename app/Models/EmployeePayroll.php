<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToTenant;

class EmployeePayroll extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'employee_id',
        'period_start',
        'period_end',
        'basic_salary',
        'per_day_salary',
        'present_days',
        'absent_days',
        'paid_absent_days',
        'weekend_days',
        'deduction_amount',
        'bonus_amount',
        'other_bonus_amount',
        'increment_amount',
        'advance_deduction',
        'gross_salary',
        'net_pay',
        'status',
        'paid_at',
        'cash_account_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'basic_salary' => 'decimal:2',
        'per_day_salary' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'other_bonus_amount' => 'decimal:2',
        'increment_amount' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
