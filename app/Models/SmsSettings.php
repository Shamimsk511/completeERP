<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SmsSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'provider_name',
        'api_token',
        'api_url',
        'sender_id',
        'is_active',
        'sms_enabled',
        'settings',
        'balance',
        'total_sent',
        'monthly_sent',
        'last_balance_check',
        'expiry_date'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sms_enabled' => 'boolean',
        'settings' => 'array',
        'balance' => 'decimal:2',
        'last_balance_check' => 'datetime',
        'expiry_date' => 'datetime'
    ];

    // Encrypt API token
    public function setApiTokenAttribute($value)
    {
        if ($value) {
            $this->attributes['api_token'] = Crypt::encrypt($value);
        }
    }

    // Decrypt API token
    public function getApiTokenAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decrypt($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    // Get the active SMS provider
    public static function getActiveProvider()
    {
        return static::where('is_active', true)
                    ->where('sms_enabled', true)
                    ->first();
    }

    // Check if SMS is globally enabled
    public static function isSmsEnabled()
    {
        return static::where('is_active', true)
                    ->where('sms_enabled', true)
                    ->exists();
    }

    // Scope for active providers
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for enabled SMS
    public function scopeEnabled($query)
    {
        return $query->where('sms_enabled', true);
    }

    // Relationships
    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class, 'provider', 'provider');
    }

    // Helper methods
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function hasLowBalance($threshold = 100)
    {
        return $this->balance < $threshold;
    }

    public function getStatusAttribute()
    {
        if (!$this->is_active) return 'inactive';
        if (!$this->sms_enabled) return 'disabled';
        if ($this->isExpired()) return 'expired';
        if ($this->hasLowBalance()) return 'low_balance';
        return 'active';
    }

    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'active': return 'success';
            case 'low_balance': return 'warning';
            case 'expired': return 'danger';
            case 'disabled': return 'secondary';
            case 'inactive': return 'dark';
            default: return 'primary';
        }
    }
}