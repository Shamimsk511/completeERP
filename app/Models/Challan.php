<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToTenant;

class Challan extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'challan_number',
        'invoice_id',
        'challan_date',
        'vehicle_number',
        'driver_name',
        'driver_phone',
        'shipping_address',
        'receiver_name',
        'receiver_phone',
        'notes',
        'delivered_at',
    ];

    protected $casts = [
        'challan_date' => 'date',
        'delivered_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(ChallanItem::class);
    }

    // Generate next challan number
    public static function getNextChallanNumber()
    {
        $lastChallan = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastChallan ? (int)substr($lastChallan->challan_number, 3) + 1 : 1000;
        return 'CH-' . $nextNumber;
    }
}
