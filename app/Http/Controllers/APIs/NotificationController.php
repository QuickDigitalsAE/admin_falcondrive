<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Notification_ViewAll', ['only' => ['getNotifications']]);
        $this->middleware('permission:Notification_ViewMine', ['only' => ['getMyNotifications']]);
        $this->middleware('permission:Notification_View', ['only' => ['editNotification']]);
        $this->middleware('permission:Notification_Add', ['only' => ['postNotification']]);
        $this->middleware('permission:Notification_Edit', ['only' => ['updateNotification']]);
        $this->middleware('permission:Notification_Delete', ['only' => ['deleteNotification']]);
        $this->middleware('permission:Notification_Revoke', ['only' => ['revokeNotification']]);
    }

    public function postNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'json' => ['nullable', function ($attribute, $value, $fail) {
                if (!is_array($value) && !is_string($value)) {
                    $fail("The {$attribute} field must be a valid JSON object or string.");
                }
            }],
            'category' => 'nullable|string|max:191',
            'type' => 'nullable|string|max:191',
            'datetime' => 'required|date',
            'status' => 'required|string',
        ]);

        $notification = Notification::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'body' => $request->body,
            'json' => is_array($request->json) ? json_encode($request->json) : $request->json,
            'category' => $request->category,
            'type' => $request->type,
            'datetime' => $request->datetime,
            'status' => $request->status,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Notification created successfully.',
            'data' => $notification,
        ], Response::HTTP_CREATED);
    }

    public function getNotifications()
    {
        $per_page = getPerPage();
        $search = request('search');
        $is_deleted = request('is_deleted');
        $is_export = request('is_export');

        $query = $is_deleted ? Notification::onlyTrashed() : Notification::query();
        $query->with(['user:id,name', 'createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->orderBy('datetime', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('body', 'LIKE', "%{$search}%")
                ->orWhere('category', 'LIKE', "%{$search}%")
                ->orWhere('type', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($n) {
            return [
                'id' => $n->id,
                'user_id' => $n->user_id,
                'user_name' => optional($n->user)->name,
                'title' => $n->title,
                'body' => $n->body,
                'json' => $n->json ? json_decode($n->json, true) : null,
                'category' => $n->category,
                'type' => $n->type,
                'status' => $n->status,
                'datetime' => $n->datetime,
                'created_by' => optional($n->createdByUser)->name,
                'updated_by' => optional($n->updatedByUser)->name,
                'deleted_by' => optional($n->deletedByUser)->name,
                'created_at' => $n->created_at,
                'updated_at' => $n->updated_at,
                'deleted_at' => $n->deleted_at
            ];
        };

        if ($is_export) {
            $records = $query->get();
            $csvHeader = ['ID', 'User Name', 'Title', 'Body', 'JSON', 'Category', 'Type', 'Status', 'DateTime', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($records, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($records as $n) {
                    $f = $format($n);
                    $row = [
                        $f['id'], $f['user_name'], $f['title'], $f['body'], json_encode($f['json']),
                        $f['category'], $f['type'], $f['status'], $f['datetime'],
                        $f['created_by'], $f['updated_by'], $f['created_at'], $f['updated_at']
                    ];
                    if ($is_deleted) {
                        $row[] = $f['deleted_by'];
                        $row[] = $f['deleted_at'];
                    }
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=notifications_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $notifications = $query->paginate($per_page);
        $formatted = $notifications->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Notifications fetched successfully.',
            'data' => [
                'list' => $formatted,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ],
        ], 200);
    }

    public function getMyNotifications()
    {
        $per_page = getPerPage();
        $search = request('search');
        $is_deleted = request('is_deleted');
        $is_export = request('is_export');
        $authUserId = Auth::id();

        $query = $is_deleted ? Notification::onlyTrashed() : Notification::query();
        $query->where('created_by', $authUserId)
            ->with(['user:id,name', 'createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->orderBy('datetime', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('body', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%")
                    ->orWhere('type', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($n) {
            return [
                'id' => $n->id,
                'user_id' => $n->user_id,
                'user_name' => optional($n->user)->name,
                'title' => $n->title,
                'body' => $n->body,
                'json' => $n->json ? json_decode($n->json, true) : null,
                'category' => $n->category,
                'type' => $n->type,
                'status' => $n->status,
                'datetime' => $n->datetime,
                'created_by' => optional($n->createdByUser)->name,
                'updated_by' => optional($n->updatedByUser)->name,
                'deleted_by' => optional($n->deletedByUser)->name,
                'created_at' => $n->created_at,
                'updated_at' => $n->updated_at,
                'deleted_at' => $n->deleted_at,
            ];
        };

        if ($is_export) {
            $records = $query->get();
            $csvHeader = ['ID', 'User Name', 'Title', 'Body', 'JSON', 'Category', 'Type', 'Status', 'DateTime', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($records, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($records as $n) {
                    $f = $format($n);
                    $row = [
                        $f['id'], $f['user_name'], $f['title'], $f['body'], json_encode($f['json']),
                        $f['category'], $f['type'], $f['status'], $f['datetime'],
                        $f['created_by'], $f['updated_by'], $f['created_at'], $f['updated_at'],
                    ];
                    if ($is_deleted) {
                        $row[] = $f['deleted_by'];
                        $row[] = $f['deleted_at'];
                    }
                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=my_notifications_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $notifications = $query->paginate($per_page);
        $formatted = $notifications->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'My notifications fetched successfully.',
            'data' => [
                'list' => $formatted,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ],
        ], 200);
    }

    public function editNotification($id)
    {
        $notification = Notification::withTrashed()
            ->with(['user:id,name', 'createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->find($id);

        if (!$notification) {
            return response()->json([
                'status' => false,
                'message' => 'Notification not found.'
            ], 404);
        }

        $notification->json = $notification->json ? json_decode($notification->json, true) : null;

        return response()->json([
            'status' => true,
            'message' => 'Notification fetched successfully.',
            'data' => [
                'id' => $notification->id,
                'user_id' => $notification->user_id,
                'user_name' => optional($notification->user)->name,
                'title' => $notification->title,
                'body' => $notification->body,
                'json' => $notification->json,
                'category' => $notification->category,
                'type' => $notification->type,
                'status' => $notification->status,
                'datetime' => $notification->datetime,
                'created_by' => optional($notification->createdByUser)->name,
                'updated_by' => optional($notification->updatedByUser)->name,
                'deleted_by' => optional($notification->deletedByUser)->name,
                'created_at' => $notification->created_at,
                'updated_at' => $notification->updated_at,
                'deleted_at' => $notification->deleted_at,
            ],
        ], 200);
    }

    public function updateNotification(Request $request, $id)
    {
        $notification = Notification::withTrashed()->findOrFail($id);

        $request->validate([
            'title'    => 'nullable|string|max:255',
            'body'     => 'nullable|string',
            'category' => 'nullable|string',
            'type'     => 'nullable|string',
            'datetime' => 'nullable|date',
            'status'   => 'nullable|string', // adjust as needed
            'json'     => 'nullable|array',
            'user_id'  => 'nullable|exists:users,id',
        ]);

        // Fill attributes excluding JSON
        $notification->fill($request->except('json'));

        // Handle JSON field
        if ($request->has('json')) {
            $notification->json = json_encode($request->json);
        }

        $notification->updated_by = Auth::id();
        $notification->save();

        return response()->json([
            'status'  => true,
            'message' => 'Notification updated successfully.',
            'data'    => $notification
        ]);
    }

    public function deleteNotification($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['status' => false, 'message' => 'Notification not found.'], 404);
        }

        $notification->deleted_by = Auth::id();
        $notification->save();
        $notification->delete();

        return response()->json([
            'status' => true,
            'message' => 'Notification deleted successfully.',
        ], 200);
    }

    public function revokeNotification($id)
    {
        $notification = Notification::onlyTrashed()->find($id);

        if (!$notification) {
            return response()->json(['status' => false, 'message' => 'Notification not found.'], 404);
        }

        $notification->restore();
        $notification->deleted_by = null;
        $notification->save();

        return response()->json([
            'status' => true,
            'message' => 'Notification restored successfully.',
        ], 200);
    }
}
