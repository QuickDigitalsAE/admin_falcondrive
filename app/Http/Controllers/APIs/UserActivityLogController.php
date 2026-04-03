<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserActivityLogController extends Controller
{
    // Get all user auth activities logs
    public function userAuthHistory()
    {
        try {
            $perPage = request()->input('per_page');
            $search = request()->query('search');
            $is_export = request()->query('is_export');

            // Base query (only login/logout)
            $activityLogsQuery = UserActivityLog::whereIn('action', ['login', 'logout'])
                                                ->orderBy('created_at', 'DESC');

            // === Search filter ===
            if (!empty($search)) {
                $matchedUserIds = User::where('name', 'LIKE', "%{$search}%")
                                    ->orWhere('email', 'LIKE', "%{$search}%")
                                    ->pluck('id');

                $activityLogsQuery->where(function ($query) use ($search, $matchedUserIds) {
                    $query->whereIn('user_id', $matchedUserIds)
                        ->orWhere('action', 'LIKE', "%{$search}%");
                });
            }

            $userActivityLogs = $activityLogsQuery->get();
            $userIds = $userActivityLogs->pluck('user_id')->unique();

            // Include soft-deleted users
            $users = User::withTrashed()->whereIn('id', $userIds)->get()->keyBy('id');

            // === Export CSV if requested ===
            if ($is_export) {
                $csvHeader = ['ID', 'User Name', 'User Email', 'Action', 'Changes', 'Created At'];

                $callback = function () use ($userActivityLogs, $users, $csvHeader) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $csvHeader);

                    foreach ($userActivityLogs as $log) {
                        $user = $users[$log->user_id] ?? null;
                        fputcsv($file, [
                            $log->id,
                            $user->name ?? '',
                            $user->email ?? '',
                            $log->action,
                            json_encode($log->changes),
                            $log->created_at->toDateTimeString(),
                        ]);
                    }

                    fclose($file);
                };

                $fileName = 'auth_logs_export_' . now()->format('Ymd_His') . '.csv';

                return response()->stream($callback, 200, [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename={$fileName}",
                ]);
            }

            // === Handle Pagination ===
            if ($perPage == 0) {
                $paginatedLogs = $userActivityLogs;
                $pagination = [
                    'current_page' => 0,
                    'last_page' => 1,
                    'per_page' => $userActivityLogs->count(),
                    'total' => $userActivityLogs->count(),
                ];
            } else {
                $paginatedLogs = $activityLogsQuery->paginate($perPage);
                $pagination = [
                    'current_page' => $paginatedLogs->currentPage(),
                    'last_page' => $paginatedLogs->lastPage(),
                    'per_page' => $paginatedLogs->perPage(),
                    'total' => $paginatedLogs->total(),
                ];
            }

            // === If no records found ===
            if ($paginatedLogs->isEmpty()) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'User Auth logs not found'
                ], 404);
            }

            // === Format final data ===
            $activityLog_Data = $paginatedLogs->map(function ($log) use ($users) {
                $user = $users[$log->user_id] ?? null;

                return [
                    'id' => $log->id,
                    'user_name' => $user->name ?? '',
                    'user_email' => $user->email ?? '',
                    'action' => $log->action,
                    'changes' => $log->changes,
                    'created_at' => $log->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'status' => 'true',
                'data' => $activityLog_Data,
                'pagination' => $pagination
            ], Response::HTTP_OK);

        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'false',
                'message' => 'An error occurred: ' . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    // Get all user activity logs on the admin side
    public function adminSideAllLogs()
    {
        try {
            $perPage = request()->input('per_page');
            $search = request()->query('search');
            $is_export = request()->query('is_export');

            // Step 1: Base query (exclude login/logout)
            $activityLogsQuery = UserActivityLog::whereNotIn('action', ['login', 'logout'])
                                                ->orderBy('id', 'DESC');

            // Step 2: Apply search filters
            if (!empty($search)) {
                $userIdsByName = User::where('name', 'LIKE', "%{$search}%")
                                    ->orWhere('email', 'LIKE', "%{$search}%")
                                    ->pluck('id');

                $activityLogsQuery->where(function ($query) use ($search, $userIdsByName) {
                    $query->whereIn('user_id', $userIdsByName)
                        ->orWhere('table_name', 'LIKE', "%{$search}%")
                        ->orWhere('action', 'LIKE', "%{$search}%");
                });
            }

            // Step 3: Fetch logs (for both export and pagination)
            $userActivityLogs = $activityLogsQuery->get();

            // Step 4: Load related users (including soft-deleted)
            $userIds = $userActivityLogs->pluck('user_id')->unique();
            $users = User::withTrashed()->whereIn('id', $userIds)->get()->keyBy('id');

            // === Export as CSV ===
            if ($is_export) {
                $csvHeader = ['ID', 'User Name', 'User Email', 'Model Type', 'Table Name', 'Action', 'Changes', 'Created At'];

                $callback = function () use ($userActivityLogs, $users, $csvHeader) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $csvHeader);

                    foreach ($userActivityLogs as $log) {
                        $user = $users[$log->user_id] ?? null;

                        fputcsv($file, [
                            $log->id,
                            $user->name ?? '',
                            $user->email ?? '',
                            $log->model_type,
                            $log->table_name,
                            $log->action,
                            json_encode($log->changes),
                            $log->created_at->toDateTimeString(),
                        ]);
                    }

                    fclose($file);
                };

                $fileName = 'admin_activity_logs_' . now()->format('Ymd_His') . '.csv';

                return response()->stream($callback, 200, [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename={$fileName}",
                ]);
            }

            // Step 5: Handle pagination (if not export)
            if ($perPage == 0) {
                $paginatedLogs = $userActivityLogs;
                $pagination = [
                    'current_page' => 0,
                    'last_page' => 1,
                    'per_page' => $userActivityLogs->count(),
                    'total' => $userActivityLogs->count(),
                ];
            } else {
                $paginatedLogs = $activityLogsQuery->paginate($perPage);
                $pagination = [
                    'current_page' => $paginatedLogs->currentPage(),
                    'last_page' => $paginatedLogs->lastPage(),
                    'per_page' => $paginatedLogs->perPage(),
                    'total' => $paginatedLogs->total(),
                ];
            }

            // Step 6: If no logs
            if ($paginatedLogs->isEmpty()) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'User activity Logs not found'
                ], 404);
            }

            // Step 7: Format log data
            $activityLog_Data = $paginatedLogs->map(function ($log) use ($users) {
                $user = $users[$log->user_id] ?? null;

                return [
                    'id' => $log->id,
                    'user_name' => $user->name ?? '',
                    'user_email' => $user->email ?? '',
                    'model_type' => $log->model_type,
                    'table_name' => $log->table_name,
                    'action' => $log->action,
                    'changes' => $log->changes,
                    'created_at' => $log->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'status' => 'true',
                'data' => $activityLog_Data,
                'pagination' => $pagination
            ], Response::HTTP_OK);

        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'false',
                'message' => 'An error occurred: ' . $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
