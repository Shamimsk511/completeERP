<?php

namespace App\Observers;

use App\Models\Challan;
use App\Services\MobileNotificationService;
use Illuminate\Support\Facades\Log;

class ChallanObserver
{
    public function __construct(protected MobileNotificationService $mobileNotificationService)
    {
    }

    public function created(Challan $challan): void
    {
        try {
            $challan->loadMissing('invoice:id,invoice_number');

            $this->mobileNotificationService->notifyEvent(
                'challan_created',
                'New Challan',
                'Challan ' . $challan->challan_number . ' created.',
                [
                    'challan_id' => $challan->id,
                    'challan_number' => $challan->challan_number,
                    'invoice_number' => $challan->invoice?->invoice_number,
                ],
                $challan->tenant_id ? (int) $challan->tenant_id : null
            );
        } catch (\Exception $e) {
            Log::warning('Failed to push mobile challan notification', [
                'challan_id' => $challan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

