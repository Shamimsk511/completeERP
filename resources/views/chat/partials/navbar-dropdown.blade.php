@php
    $count = $notifications->count();
@endphp
<div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
    <strong>Chat</strong>
    @if($count)
        <button type="button" class="btn btn-sm btn-link js-chat-mark-read">Mark all read</button>
    @endif
</div>
<div class="px-3 pt-2">
    <a href="{{ route('chat.index') }}" class="btn btn-sm btn-outline-primary btn-block">View all</a>
</div>
@if($count === 0)
    <div class="px-3 py-2 text-muted text-center">No unread messages.</div>
@else
    @foreach($notifications as $notification)
        @php
            $isBroadcast = (bool) ($notification->data['broadcast'] ?? false);
            $senderName = $notification->data['sender_name'] ?? 'Unknown';
            $message = $notification->data['message'] ?? '';
            $timestamp = $notification->created_at?->format('d M, h:i A');
            $senderId = $notification->data['sender_id'] ?? null;
            $link = $isBroadcast
                ? route('chat.index', ['broadcast' => 1])
                : ($senderId ? route('chat.index', ['user_id' => $senderId]) : route('chat.index'));
        @endphp
        <a href="{{ $link }}" class="dropdown-item js-chat-notification" data-read-url="{{ route('chat.notifications.read-one', $notification->id) }}">
            <div class="d-flex justify-content-between align-items-center">
                <strong>{{ $isBroadcast ? 'Broadcast' : $senderName }}</strong>
                <small class="text-muted">{{ $timestamp }}</small>
            </div>
            <div class="text-muted text-truncate">{{ $message }}</div>
        </a>
    @endforeach
@endif
