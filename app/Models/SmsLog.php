<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'phone',
        'message',
        'status',
        'response',
        'reference_id',
        'cost',
        'sendable_type',
        'sendable_id',
        'user_id'
    ];

    protected $casts = [
        'cost' => 'decimal:4',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Polymorphic relationship
    public function sendable()
    {
        return $this->morphTo();
    }

    // User relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // SMS Settings relationship
    public function smsSettings()
    {
        return $this->belongsTo(SmsSettings::class, 'provider', 'provider');
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    // Status helpers
    public function isSuccessful()
    {
        return $this->status === 'sent';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    // Status badge color
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'sent': return 'success';
            case 'failed': return 'danger';
            case 'pending': return 'warning';
            default: return 'secondary';
        }
    }

    // Clean phone number display
    public function getFormattedPhoneAttribute()
    {
        $phone = $this->phone;
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '01') {
            return substr($phone, 0, 3) . '-' . substr($phone, 3, 4) . '-' . substr($phone, 7);
        }
        return $phone;
    }

    // Truncated message for display
    public function getTruncatedMessageAttribute($length = 50)
    {
        return strlen($this->message) > $length 
            ? substr($this->message, 0, $length) . '...' 
            : $this->message;
    }
}