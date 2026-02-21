<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MobileEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $eventType,
        protected string $title,
        protected string $message,
        protected array $meta = []
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'event_type' => $this->eventType,
            'title' => $this->title,
            'message' => $this->message,
            'meta' => $this->meta,
            'created_at' => now()->toISOString(),
        ];
    }
}

