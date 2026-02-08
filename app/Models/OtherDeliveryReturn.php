<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class OtherDeliveryReturn extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'return_number',
        'return_date',
        'returner_name',
        'returner_address',
        'returner_phone',
        'notes',
        'status',
        'received_by'
    ];
protected $casts = [
        'return_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function items()
    {
        return $this->hasMany(OtherDeliveryReturnItem::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public static function getNextReturnNumber()
    {
        $lastReturn = self::orderBy('id', 'desc')->first();
        $number = $lastReturn ? intval(substr($lastReturn->return_number, 3)) + 1 : 1;
        return 'RTN' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
