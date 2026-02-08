<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;

class CallLog extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'customer_id', 'user_id', 'call_status', 'duration',
        'notes', 'payment_promise_date', 'follow_up_required',
        'called_at', 'outcome'
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'payment_promise_date' => 'date',
        'follow_up_required' => 'boolean',
        'duration' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope for successful calls
    public function scopeSuccessful($query)
    {
        return $query->where('call_status', 'successful');
    }

    // Scope for recent calls
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('called_at', '>=', now()->subDays($days));
    }
}
