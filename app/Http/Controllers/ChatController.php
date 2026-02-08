<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\ChatMessageNotification;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:chat-access')->only(['index', 'messages', 'presence', 'ping', 'notifications', 'navbarNotifications']);
        $this->middleware('permission:chat-message-send')->only(['send']);
        $this->middleware('permission:chat-clear')->only(['clear']);
    }

    public function index(Request $request)
    {
        $tenantId = TenantContext::currentId();
        if (!$tenantId) {
            return redirect()->route('dashboard')
                ->with('error', 'Please select a tenant before using chat.');
        }

        $users = $this->tenantUsers($tenantId)
            ->whereKeyNot(auth()->id())
            ->orderBy('name')
            ->get();

        $selectedUserId = null;
        $broadcastSelected = false;

        if ($request->boolean('broadcast')) {
            $broadcastSelected = true;
        } elseif ($request->filled('user_id')) {
            $candidate = User::find($request->integer('user_id'));
            if ($candidate && $candidate->id !== auth()->id() && $this->isUserInTenant($candidate, $tenantId)) {
                $selectedUserId = $candidate->id;
            }
        }

        return view('chat.index', compact('users', 'selectedUserId', 'broadcastSelected'));
    }

    public function messages(string $target, Request $request)
    {
        $tenantId = TenantContext::currentId();
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant not set.'], 403);
        }

        $authId = auth()->id();
        $afterId = (int) $request->get('after_id', 0);

        if ($target === 'all') {
            $query = ChatMessage::where('tenant_id', $tenantId)
                ->where('recipient_id', $authId)
                ->whereNotNull('broadcast_key')
                ->with('sender')
                ->orderBy('id');

            if ($afterId > 0) {
                $query->where('id', '>', $afterId);
            }

            $messages = $query->get();

            ChatMessage::where('tenant_id', $tenantId)
                ->where('recipient_id', $authId)
                ->whereNotNull('broadcast_key')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $payload = $messages->map(function (ChatMessage $message) use ($authId) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'recipient_id' => $message->recipient_id,
                    'message' => $message->message,
                    'created_at' => $message->created_at?->toDateTimeString(),
                    'sender_name' => $message->sender?->name,
                    'is_mine' => $message->sender_id === $authId,
                    'is_broadcast' => true,
                ];
            });

            return response()->json([
                'messages' => $payload,
                'last_id' => $messages->last()?->id,
            ]);
        }

        $user = User::find($target);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if (!$this->isUserInTenant($user, $tenantId)) {
            return response()->json(['message' => 'User not available for this tenant.'], 403);
        }

        if ($user->id === $authId) {
            return response()->json(['message' => 'Cannot open self chat.'], 422);
        }

        $query = ChatMessage::where('tenant_id', $tenantId)
            ->whereNull('broadcast_key')
            ->where(function ($q) use ($authId, $user) {
                $q->where('sender_id', $authId)
                    ->where('recipient_id', $user->id)
                    ->orWhere(function ($q) use ($authId, $user) {
                        $q->where('sender_id', $user->id)
                            ->where('recipient_id', $authId);
                    });
            })
            ->with('sender')
            ->orderBy('id');

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        }

        $messages = $query->get();

        ChatMessage::where('tenant_id', $tenantId)
            ->whereNull('broadcast_key')
            ->where('sender_id', $user->id)
            ->where('recipient_id', $authId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $payload = $messages->map(function (ChatMessage $message) use ($authId) {
            return [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'recipient_id' => $message->recipient_id,
                'message' => $message->message,
                'created_at' => $message->created_at?->toDateTimeString(),
                'sender_name' => $message->sender?->name,
                'is_mine' => $message->sender_id === $authId,
                'is_broadcast' => false,
            ];
        });

        return response()->json([
            'messages' => $payload,
            'last_id' => $messages->last()?->id,
        ]);
    }

    public function send(Request $request)
    {
        $tenantId = TenantContext::currentId();
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant not set.'], 403);
        }

        $senderId = auth()->id();
        $broadcastAll = $request->boolean('broadcast_all') || $request->input('recipient_id') === 'all';

        if ($broadcastAll) {
            $validated = $request->validate([
                'message' => 'required|string|max:2000',
            ]);
        } else {
            $validated = $request->validate([
                'recipient_id' => 'required|integer|exists:users,id',
                'message' => 'required|string|max:2000',
            ]);
        }

        if ($broadcastAll) {
            $broadcastKey = (string) Str::uuid();
            $senderCopy = null;

            DB::transaction(function () use ($validated, $tenantId, $senderId, $broadcastKey, &$senderCopy) {
                $senderCopy = ChatMessage::create([
                    'tenant_id' => $tenantId,
                    'sender_id' => $senderId,
                    'recipient_id' => $senderId,
                    'message' => $validated['message'],
                    'broadcast_key' => $broadcastKey,
                    'read_at' => now(),
                ]);

                $recipients = $this->tenantUsers($tenantId)
                    ->whereKeyNot($senderId)
                    ->get();

                foreach ($recipients as $recipient) {
                    $message = ChatMessage::create([
                        'tenant_id' => $tenantId,
                        'sender_id' => $senderId,
                        'recipient_id' => $recipient->id,
                        'message' => $validated['message'],
                        'broadcast_key' => $broadcastKey,
                    ]);

                    $recipient->notify(new ChatMessageNotification($message));
                }
            });

            return response()->json([
                'id' => $senderCopy->id,
                'sender_id' => $senderCopy->sender_id,
                'recipient_id' => $senderCopy->recipient_id,
                'message' => $senderCopy->message,
                'created_at' => $senderCopy->created_at?->toDateTimeString(),
                'sender_name' => $senderCopy->sender?->name,
                'is_mine' => true,
                'is_broadcast' => true,
            ]);
        }

        if (empty($validated['recipient_id'])) {
            return response()->json(['message' => 'Recipient is required.'], 422);
        }

        $recipient = User::findOrFail($validated['recipient_id']);
        if (!$this->isUserInTenant($recipient, $tenantId)) {
            return response()->json(['message' => 'User not available for this tenant.'], 403);
        }

        if ($recipient->id === $senderId) {
            return response()->json(['message' => 'Cannot send message to yourself.'], 422);
        }

        $message = null;

        DB::transaction(function () use ($validated, $tenantId, $senderId, $recipient, &$message) {
            $message = ChatMessage::create([
                'tenant_id' => $tenantId,
                'sender_id' => $senderId,
                'recipient_id' => $recipient->id,
                'message' => $validated['message'],
            ]);

            $recipient->notify(new ChatMessageNotification($message));
        });

        return response()->json([
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'recipient_id' => $message->recipient_id,
            'message' => $message->message,
            'created_at' => $message->created_at?->toDateTimeString(),
            'sender_name' => $message->sender?->name,
            'is_mine' => true,
            'is_broadcast' => false,
        ]);
    }

    public function presence()
    {
        $tenantId = TenantContext::currentId();
        if (!$tenantId) {
            return response()->json([
                'online_user_ids' => [],
                'unread_by_sender' => [],
                'notification_count' => 0,
            ]);
        }

        $onlineThreshold = Carbon::now()->subMinutes(5);

        $onlineUserIds = $this->tenantUsers($tenantId)
            ->where('last_seen_at', '>=', $onlineThreshold)
            ->pluck('id')
            ->values();

        $unreadBySender = ChatMessage::where('tenant_id', $tenantId)
            ->where('recipient_id', auth()->id())
            ->whereNull('read_at')
            ->whereNull('broadcast_key')
            ->selectRaw('sender_id, COUNT(*) as total')
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        $broadcastUnread = ChatMessage::where('tenant_id', $tenantId)
            ->where('recipient_id', auth()->id())
            ->whereNull('read_at')
            ->whereNotNull('broadcast_key')
            ->count();

        $notificationCount = auth()->user()
            ?->unreadNotifications()
            ->where('type', ChatMessageNotification::class)
            ->count() ?? 0;

        return response()->json([
            'online_user_ids' => $onlineUserIds,
            'unread_by_sender' => $unreadBySender,
            'broadcast_unread' => $broadcastUnread,
            'notification_count' => $notificationCount,
        ]);
    }

    public function ping()
    {
        $user = auth()->user();
        if ($user) {
            $user->forceFill(['last_seen_at' => now()])->save();
        }

        return response()->json(['success' => true]);
    }

    public function notifications()
    {
        $notifications = auth()->user()
            ?->unreadNotifications()
            ->where('type', ChatMessageNotification::class)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'sender_name' => $notification->data['sender_name'] ?? 'Unknown',
                    'message' => $notification->data['message'] ?? '',
                    'created_at' => $notification->created_at?->toDateTimeString(),
                    'sender_id' => $notification->data['sender_id'] ?? null,
                    'broadcast' => (bool) ($notification->data['broadcast'] ?? false),
                ];
            }) ?? collect();

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    public function navbarNotifications()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['label' => 0]);
        }

        $totalUnread = $user->unreadNotifications()
            ->where('type', ChatMessageNotification::class)
            ->count();

        $notifications = $user->unreadNotifications()
            ->where('type', ChatMessageNotification::class)
            ->latest()
            ->limit(5)
            ->get();

        $dropdown = view('chat.partials.navbar-dropdown', [
            'notifications' => $notifications,
        ])->render();

        return response()->json([
            'label' => $totalUnread,
            'label_color' => $totalUnread > 0 ? 'danger' : 'secondary',
            'icon_color' => $totalUnread > 0 ? 'warning' : 'white',
            'dropdown' => $dropdown,
        ]);
    }

    public function markNotificationsRead()
    {
        auth()->user()
            ?->unreadNotifications()
            ->where('type', ChatMessageNotification::class)
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markNotificationRead(string $notificationId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $updated = $user->notifications()
            ->where('id', $notificationId)
            ->where('type', ChatMessageNotification::class)
            ->update(['read_at' => now()]);

        return response()->json(['success' => $updated > 0]);
    }

    public function clear(Request $request)
    {
        $tenantId = TenantContext::currentId();
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant not set.'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'broadcast' => 'nullable|boolean',
        ]);

        $query = ChatMessage::where('tenant_id', $tenantId);

        if (!empty($validated['user_id'])) {
            $user = User::find($validated['user_id']);
            if (!$user || !$this->isUserInTenant($user, $tenantId)) {
                return response()->json(['message' => 'User not available for this tenant.'], 403);
            }

            $authId = auth()->id();
            $query->whereNull('broadcast_key')
                ->where(function ($q) use ($authId, $user) {
                    $q->where('sender_id', $authId)
                        ->where('recipient_id', $user->id)
                        ->orWhere(function ($q) use ($authId, $user) {
                            $q->where('sender_id', $user->id)
                                ->where('recipient_id', $authId);
                        });
                });
        } elseif ($request->boolean('broadcast')) {
            $query->whereNotNull('broadcast_key');
        }

        $deleted = $query->delete();

        $tenantUserIds = $this->tenantUsers($tenantId)->pluck('id')->all();
        if (!empty($tenantUserIds)) {
            DB::table('notifications')
                ->where('type', ChatMessageNotification::class)
                ->where('notifiable_type', User::class)
                ->whereIn('notifiable_id', $tenantUserIds)
                ->whereJsonContains('data->tenant_id', $tenantId)
                ->delete();
        }

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    protected function tenantUsers(int $tenantId)
    {
        return User::query()->where(function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId)
                ->orWhereHas('tenants', function ($tenantQuery) use ($tenantId) {
                    $tenantQuery->where('tenants.id', $tenantId);
                });
        });
    }

    protected function isUserInTenant(User $user, int $tenantId): bool
    {
        if ((int) $user->tenant_id === (int) $tenantId) {
            return true;
        }

        return $user->tenants()->whereKey($tenantId)->exists();
    }
}
