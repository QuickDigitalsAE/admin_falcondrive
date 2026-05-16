<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SystemVisibility;
use App\Models\UserActivityLog;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user()?->loadMissing('roles');

            if (!SystemVisibility::isSuperAdminUser($user)) {
                abort(403, 'Only Super Admin can access activity logs.');
            }

            return $next($request);
        });

        $this->middleware('permission:ActivityLogs_ViewAll|ActivityLogs_View', ['only' => ['index']]);
        $this->middleware('permission:ActivityLogs_ViewAll|ActivityLogs_View', ['only' => ['show']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 12);
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', 'all'));
        $action = trim((string) $request->query('action', 'all'));
        $isExport = $request->boolean('is_export');

        $logsQuery = UserActivityLog::query()->with('user');
        $this->applyFilters($logsQuery, $search, $category, $action);
        $stats = $this->buildStats($search, $category, $action);
        $logsQuery->orderByDesc('created_at');

        if ($isExport) {
            return $this->exportLogs($logsQuery->get());
        }

        $logs = $logsQuery->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();

            return response()->json([
                'status' => true,
                'message' => 'Activity logs fetched successfully.',
                'data' => [
                    'items' => $logs->getCollection()->map(function (UserActivityLog $log) use ($authUser) {
                        return $this->mapLog($log, $authUser->can('ActivityLogs_ViewAll') || $authUser->can('ActivityLogs_View'));
                    })->values(),
                    'pagination' => [
                        'current_page' => $logs->currentPage(),
                        'last_page' => $logs->lastPage(),
                        'per_page' => $logs->perPage(),
                        'total' => $logs->total(),
                        'from' => $logs->firstItem(),
                        'to' => $logs->lastItem(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'category' => $category,
                        'action' => $action,
                    ],
                    'stats' => $stats,
                ],
            ]);
        }

        return view('admin.activity-logs.index', [
            'actions' => $this->actionOptions(),
        ]);
    }

    public function show(int $id)
    {
        $log = UserActivityLog::with('user')->find($id);

        if (!$log) {
            return redirect()->route('admin.activity-logs')->with('error', 'Activity log not found.');
        }

        return view('admin.activity-logs.show', [
            'log' => $log,
            'meta' => $this->mapLog($log, true),
        ]);
    }

    private function mapLog(UserActivityLog $log, bool $canView): array
    {
        return [
            'id' => $log->id,
            'category' => ActivityLogHelper::category($log),
            'action' => $log->action,
            'action_label' => ActivityLogHelper::actionLabel($log->action),
            'module_label' => ActivityLogHelper::moduleLabel($log),
            'summary' => ActivityLogHelper::summary($log),
            'details' => ActivityLogHelper::detailLines($log),
            'performed_by' => $log->user?->name ?: (($log->changes['user_name'] ?? null) ?: 'System'),
            'performed_by_email' => $log->user?->email ?: ($log->changes['email'] ?? null),
            'created_at_human' => optional($log->created_at)->format('d M Y, h:i A'),
            'created_at_iso' => optional($log->created_at)->toDateTimeString(),
            'show_url' => route('admin.activity-logs.show', $log->id),
            'permissions' => [
                'can_view' => $canView,
            ],
        ];
    }

    private function exportLogs($logs)
    {
        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Category', 'Action', 'Module', 'Summary', 'Performed By', 'Email', 'Details', 'Created At']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    ActivityLogHelper::category($log),
                    ActivityLogHelper::actionLabel($log->action),
                    ActivityLogHelper::moduleLabel($log),
                    ActivityLogHelper::summary($log),
                    $log->user?->name ?: (($log->changes['user_name'] ?? null) ?: 'System'),
                    $log->user?->email ?: ($log->changes['email'] ?? null),
                    ActivityLogHelper::exportDetail($log),
                    optional($log->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=activity_logs.csv',
        ]);
    }

    private function actionOptions(): array
    {
        return [
            'all' => 'All Actions',
            'login' => 'Login',
            'logout' => 'Logout',
            'failed_login' => 'Failed Login',
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
        ];
    }

    private function applyFilters($query, string $search, string $category, string $action): void
    {
        if ($category === 'auth') {
            $query->whereIn('action', ['login', 'logout', 'failed_login']);
        } elseif ($category === 'activity') {
            $query->whereNotIn('action', ['login', 'logout', 'failed_login']);
        }

        if ($action !== '' && $action !== 'all') {
            $query->where('action', $action);
        }

        if ($search !== '') {
            $query->where(function ($nestedQuery) use ($search) {
                $nestedQuery->where('action', 'LIKE', "%{$search}%")
                    ->orWhere('table_name', 'LIKE', "%{$search}%")
                    ->orWhere('model_type', 'LIKE', "%{$search}%")
                    ->orWhere('changes', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->withTrashed()
                            ->where(function ($userNestedQuery) use ($search) {
                                $userNestedQuery->where('name', 'LIKE', "%{$search}%")
                                    ->orWhere('email', 'LIKE', "%{$search}%");
                            });
                    });
            });
        }
    }

    private function buildStats(string $search, string $category, string $action): array
    {
        $baseQuery = UserActivityLog::query();
        $this->applyFilters($baseQuery, $search, $category, $action);

        $authQuery = clone $baseQuery;
        $systemQuery = clone $baseQuery;

        return [
            'total' => (clone $baseQuery)->count(),
            'auth' => $authQuery->whereIn('action', ['login', 'logout', 'failed_login'])->count(),
            'activity' => $systemQuery->whereNotIn('action', ['login', 'logout', 'failed_login'])->count(),
        ];
    }
}
