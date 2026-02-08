<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Carbon\Carbon;

class OtherDelivery extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'challan_number',
        'delivery_date',
        'recipient_name',
        'recipient_address',
        'recipient_phone',
        'vehicle_type',
        'vehicle_number',
        'driver_name',
        'driver_phone',
        'notes',
        'status',
        'delivered_by'
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(OtherDeliveryItem::class);
    }

    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public static function getNextChallanNumber()
    {
        $prefix = 'OD-';
        $year = date('Y');
        $month = date('m');
        
        $lastChallan = self::where('challan_number', 'like', $prefix . $year . $month . '%')
                           ->orderBy('id', 'desc')
                           ->first();
        
        if ($lastChallan) {
            $lastNumber = intval(substr($lastChallan->challan_number, -4));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
