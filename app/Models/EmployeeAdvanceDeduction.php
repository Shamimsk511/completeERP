<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToTenant;

class EmployeeAdvanceDeduction extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'employee_advance_id',
        'employee_payroll_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function advance()
    {
        return $this->belongsTo(EmployeeAdvance::class, 'employee_advance_id');
    }

    public function payroll()
    {
        return $this->belongsTo(EmployeePayroll::class, 'employee_payroll_id');
    }
}
