<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\MobileEventNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class MobileNotificationService
{
    public function notifyEvent(
        string $eventType,
        string $title,
        string $message,
        array $meta = [],
        ?int $tenantId = null
    ): void {
        $users = $this->targetUsers($tenantId);
        if ($users->isEmpty()) {
            return;
        }

        Notification::send(
            $users,
            new MobileEventNotification($eventType, $title, $message, $meta)
        );
    }

    protected function targetUsers(?int $tenantId): Collection
    {
        $query = User::query()->where('mobile_push_enabled', true);

        if ($tenantId) {
            $query->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'Super Admin');
                    });
            });
        }

        return $query->get();
    }
}
