<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Models\Concerns\BelongsToTenant;

class DebtCollectionTracking extends Model
{
    use BelongsToTenant;
    protected $table = 'debt_collection_trackings';

    protected $fillable = [
        'customer_id', 'due_date', 'last_call_date', 
        'calls_made', 'missed_calls', 'priority', 
        'notes', 'payment_promise_date', 'follow_up_date'
    ];

    protected $casts = [
        'due_date' => 'date',
        'last_call_date' => 'date',
        'payment_promise_date' => 'date',
        'follow_up_date' => 'date',
        'calls_made' => 'integer',
        'missed_calls' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Scope for due today
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', Carbon::today());
    }

    // Scope for overdue
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::today());
    }

    // Scope for high priority
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    // Get success rate
    public function getSuccessRateAttribute(): float
    {
        $total = $this->calls_made;
        if ($total === 0) return 0;
        
        $successful = $total - $this->missed_calls;
        return round(($successful / $total) * 100, 2);
    }
}
