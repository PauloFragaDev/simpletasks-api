<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * List notifications.
     *
     * @group Notifications
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return response()->json($notifications);
    }

    /**
     * Mark a notification as read.
     *
     * @group Notifications
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = DatabaseNotification::find($id);

        if (! $notification || $notification->notifiable_id !== $request->user()->id) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $notification->markAsRead();

        return $this->success(message: 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     *
     * @group Notifications
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return $this->success(message: 'All notifications marked as read.');
    }
}
