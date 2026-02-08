<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Accounting\Account;
use App\Models\Concerns\BelongsToTenant;

class Employee extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'email',
        'address',
        'nid',
        'photo_path',
        'file_path',
        'basic_salary',
        'join_date',
        'status',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'join_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function advances()
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    public function adjustments()
    {
        return $this->hasMany(EmployeeAdjustment::class);
    }

    public function payrolls()
    {
        return $this->hasMany(EmployeePayroll::class);
    }

    public function ledgerAccount()
    {
        return $this->hasOne(Account::class, 'linkable_id')
            ->where('linkable_type', 'employee');
    }
}
