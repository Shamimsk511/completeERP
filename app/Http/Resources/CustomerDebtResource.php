<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerDebtResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'outstanding_balance' => $this->outstanding_balance ?? 0,
            'due_date' => $this->debtCollectionTracking?->due_date,
            'priority' => $this->debtCollectionTracking?->priority ?? 'medium',
            'calls_made' => $this->debtCollectionTracking?->calls_made ?? 0,
            'missed_calls' => $this->debtCollectionTracking?->missed_calls ?? 0,
            'last_call_date' => $this->debtCollectionTracking?->last_call_date,
            'payment_promise_date' => $this->debtCollectionTracking?->payment_promise_date,
            'follow_up_date' => $this->debtCollectionTracking?->follow_up_date,
            'notes' => $this->debtCollectionTracking?->notes,
        ];
    }
}
