<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationPreferenceResource;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->select([
                'id',
                'type',
                'notifiable_type',
                'notifiable_id',
                'data',
                'read_at',
                'created_at',
            ])
            ->latest('created_at')
            ->paginate(20);

        return response()->json(
            $notifications->through(
                fn ($notification) => NotificationResource::make($notification)->resolve()
            )
        );
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }

    public function preferences(Request $request): JsonResponse
    {
        return response()->json([
            'preferences' => NotificationPreferenceResource::make(
                $request->user()
                    ->notificationPreferences()
                    ->apiSelect()
                    ->first()
            )->resolve(),
        ]);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expiration_reminders' => 'sometimes|boolean',
            'status_updates' => 'sometimes|boolean',
            'document_requests' => 'sometimes|boolean',
            'payment_confirmations' => 'sometimes|boolean',
            'office_announcements' => 'sometimes|boolean',
            'email_enabled' => 'sometimes|boolean',
            'sms_enabled' => 'sometimes|boolean',
            'push_enabled' => 'sometimes|boolean',
        ]);

        $request->user()->notificationPreferences()->update($validated);

        return response()->json([
            'preferences' => NotificationPreferenceResource::make(
                $request->user()
                    ->notificationPreferences()
                    ->apiSelect()
                    ->first()
            )->resolve(),
        ]);
    }
}
