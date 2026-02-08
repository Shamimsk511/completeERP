<?php

namespace App\Notifications;

use App\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChatMessageNotification extends Notification
{
    use Queueable;

    public function __construct(protected ChatMessage $message)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender?->name,
            'recipient_id' => $this->message->recipient_id,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at?->toDateTimeString(),
            'tenant_id' => $this->message->tenant_id,
            'broadcast' => !empty($this->message->broadcast_key),
            'broadcast_key' => $this->message->broadcast_key,
        ];
    }
}
