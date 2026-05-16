<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Support\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 12);
        $filter = (string) $request->query('filter', 'all');
        $user = Auth::user();

        $query = $user
            ->adminNotifications()
            ->latest();

        if ($filter === 'unread') {
            $query->unread();
        }

        $visibleNotifications = $query
            ->get()
            ->filter(fn (AdminNotification $notification) => AdminNotificationService::canUserViewNotification($user, $notification))
            ->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $notifications = new LengthAwarePaginator(
            $visibleNotifications->forPage($currentPage, $perPage)->values(),
            $visibleNotifications->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Notifications fetched successfully.',
                'data' => [
                    'items' => $notifications->getCollection()
                        ->map(fn (AdminNotification $notification) => AdminNotificationService::formatForUi($notification))
                        ->values(),
                    'pagination' => [
                        'current_page' => $notifications->currentPage(),
                        'last_page' => $notifications->lastPage(),
                        'per_page' => $notifications->perPage(),
                        'total' => $notifications->total(),
                        'from' => $notifications->firstItem(),
                        'to' => $notifications->lastItem(),
                    ],
                    'meta' => [
                        'unread_count' => $user->adminNotifications()
                            ->unread()
                            ->get()
                            ->filter(fn (AdminNotification $notification) => AdminNotificationService::canUserViewNotification($user, $notification))
                            ->count(),
                    ],
                ],
            ]);
        }

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
        ]);
    }

    public function recent(): JsonResponse
    {
        $notifications = Auth::user()
            ->adminNotifications()
            ->unread()
            ->latest()
            ->get();

        $user = Auth::user();
        $visibleNotifications = $notifications
            ->filter(fn (AdminNotification $notification) => AdminNotificationService::canUserViewNotification($user, $notification))
            ->take(8)
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Recent notifications fetched successfully.',
            'data' => [
                'items' => $visibleNotifications->map(fn (AdminNotification $notification) => AdminNotificationService::formatForUi($notification))->values(),
                'meta' => [
                    'total' => $visibleNotifications->count(),
                    'unread_count' => $user->adminNotifications()
                        ->unread()
                        ->get()
                        ->filter(fn (AdminNotification $notification) => AdminNotificationService::canUserViewNotification($user, $notification))
                        ->count(),
                ],
            ],
        ]);
    }

    public function markAsRead(AdminNotification $notification, Request $request): JsonResponse|RedirectResponse
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        $notification->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Notification marked as read.',
                'data' => [
                    'unread_count' => Auth::user()->adminNotifications()->unread()->count(),
                ],
            ]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        Auth::user()
            ->adminNotifications()
            ->unread()
            ->update(['read_at' => now()]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'All notifications marked as read.',
                'data' => [
                    'unread_count' => 0,
                ],
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
