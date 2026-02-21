<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $unreadOnly = filter_var($request->input('unread_only', false), FILTER_VALIDATE_BOOLEAN);
        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        $query = $user->notifications()->latest();
        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $notifications->getCollection()->map(function ($notification) {
                    $data = (array) ($notification->data ?? []);
                    return [
                        'id' => $notification->id,
                        'title' => (string) ($data['title'] ?? 'Notification'),
                        'message' => (string) ($data['message'] ?? ''),
                        'event_type' => (string) ($data['event_type'] ?? 'general'),
                        'meta' => (array) ($data['meta'] ?? []),
                        'read_at' => optional($notification->read_at)->toISOString(),
                        'created_at' => optional($notification->created_at)->toISOString(),
                    ];
                })->values(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ],
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $count],
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notification_id' => 'required|string',
        ]);

        $notification = $request->user()
            ->notifications()
            ->whereKey($validated['notification_id'])
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function settings(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'mobile_push_enabled' => (bool) $request->user()->mobile_push_enabled,
            ],
        ]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile_push_enabled' => 'required|boolean',
        ]);

        $user = $request->user();
        $user->mobile_push_enabled = (bool) $validated['mobile_push_enabled'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated.',
            'data' => [
                'mobile_push_enabled' => (bool) $user->mobile_push_enabled,
            ],
        ]);
    }
}

