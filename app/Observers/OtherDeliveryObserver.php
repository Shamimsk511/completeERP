<?php

namespace App\Observers;

use App\Models\OtherDelivery;
use App\Services\MobileNotificationService;
use Illuminate\Support\Facades\Log;

class OtherDeliveryObserver
{
    public function __construct(protected MobileNotificationService $mobileNotificationService)
    {
    }

    public function created(OtherDelivery $delivery): void
    {
        try {
            $this->mobileNotificationService->notifyEvent(
                'other_delivery_created',
                'New Other Delivery',
                'Other delivery ' . $delivery->challan_number . ' created.',
                [
                    'other_delivery_id' => $delivery->id,
                    'challan_number' => $delivery->challan_number,
                    'recipient_name' => $delivery->recipient_name,
                ],
                $delivery->tenant_id ? (int) $delivery->tenant_id : null
            );
        } catch (\Exception $e) {
            Log::warning('Failed to push mobile other delivery notification', [
                'other_delivery_id' => $delivery->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

