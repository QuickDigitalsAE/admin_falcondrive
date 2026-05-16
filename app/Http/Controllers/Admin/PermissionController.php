<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Support\RolePermissionMatrix;
use App\Support\SystemVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Permissions_ViewAll', ['only' => ['index']]);
        $this->middleware('permission:Permissions_ViewAll|Permissions_View', ['only' => ['show']]);
        $this->middleware('permission:Permissions_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Permissions_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Permissions_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:Permissions_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->boolean('is_export');

        $permissionsQuery = $isDeleted ? Permission::onlyTrashed() : Permission::query();

        $permissionsQuery->withCount([
            'roles as roles_count' => function ($query) {
                $query->where('roles.id', '!=', SystemVisibility::superAdminRoleId());
            },
        ])->orderByDesc('created_at');

        if ($search !== '') {
            $permissionsQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('table_name', 'LIKE', "%{$search}%");
            });
        }

        if ($isExport) {
            return $this->exportPermissions($permissionsQuery, $isDeleted);
        }

        $permissions = $permissionsQuery->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();

            return response()->json([
                'status' => true,
                'message' => 'Permissions fetched successfully.',
                'data' => [
                    'items' => $permissions->getCollection()->map(function (Permission $permission) use ($authUser) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'group' => Str::before($permission->name, '_'),
                            'table_name' => $permission->table_name,
                            'roles_count' => $permission->roles_count ?? 0,
                            'allowed_levels' => RolePermissionMatrix::allowedLevels($permission->name),
                            'deleted_at' => optional($permission->deleted_at)->toDateTimeString(),
                            'created_at_human' => optional($permission->created_at)->format('d M Y, h:i A'),
                            'show_url' => route('admin.permissions.show', $permission->id),
                            'edit_url' => route('admin.permissions.edit', $permission->id),
                            'delete_url' => route('admin.permissions.delete', $permission->id),
                            'restore_url' => route('admin.permissions.restore', $permission->id),
                            'permissions' => [
                                'can_view' => $authUser->can('Permissions_ViewAll') || $authUser->can('Permissions_View'),
                                'can_edit' => $authUser->can('Permissions_Edit'),
                                'can_delete' => $authUser->can('Permissions_Delete'),
                                'can_restore' => $authUser->can('Permissions_Revoke'),
                            ],
                        ];
                    })->values(),
                    'pagination' => [
                        'current_page' => $permissions->currentPage(),
                        'last_page' => $permissions->lastPage(),
                        'per_page' => $permissions->perPage(),
                        'total' => $permissions->total(),
                        'from' => $permissions->firstItem(),
                        'to' => $permissions->lastItem(),
                    ],
                ],
            ]);
        }

        return view('admin.permissions.index');
    }

    public function create()
    {
        return view('admin.permissions.create', [
            'roleLevels' => RolePermissionMatrix::levels(),
            'actionOptions' => $this->actionOptions(),
            'tableOptions' => $this->tableOptions(),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->filled('name') && !$request->has('actions')) {
            $validated = $this->validatePermission($request);

            Permission::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
                'table_name' => $validated['table_name'] ?? null,
            ]);

            return redirect()->route('admin.permissions')->with('success', 'Permission created successfully.');
        }

        $validated = $request->validate([
            'module_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z][A-Za-z0-9\\s_-]*$/'],
            'table_name' => ['nullable', 'string', 'max:100'],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*' => ['required', Rule::in($this->actionOptions())],
        ], [
            'module_name.regex' => 'Module name must start with a letter and may contain letters, numbers, spaces, underscores, or hyphens.',
            'actions.required' => 'Select at least one action.',
            'actions.min' => 'Select at least one action.',
        ]);

        $moduleName = Str::studly((string) $validated['module_name']);
        $tableName = $validated['table_name'] ?? null;
        $actions = array_values(array_unique($validated['actions']));

        $createdCount = 0;
        $restoredCount = 0;
        $updatedCount = 0;

        foreach ($actions as $action) {
            $permissionName = $moduleName . '_' . $action;

            $existing = Permission::withTrashed()
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->first();

            if (!$existing) {
                Permission::create([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                    'table_name' => $tableName,
                ]);
                $createdCount++;
                continue;
            }

            if ($existing->trashed()) {
                $existing->restore();
                $existing->deleted_by = null;
                $restoredCount++;
            }

            if (($existing->table_name ?? null) !== $tableName) {
                $existing->table_name = $tableName;
                $updatedCount++;
            }

            $existing->save();
        }

        $message = "{$createdCount} permission(s) created";
        if ($restoredCount > 0) {
            $message .= ", {$restoredCount} restored";
        }
        if ($updatedCount > 0) {
            $message .= ", {$updatedCount} updated";
        }
        $message .= '.';

        return redirect()->route('admin.permissions')->with('success', $message);

    }

    public function show(int $id)
    {
        $permission = Permission::withTrashed()->with([
            'roles' => function ($query) {
                $query->where('roles.id', '!=', SystemVisibility::superAdminRoleId());
            },
        ])->find($id);

        if (!$permission) {
            return redirect()->route('admin.permissions')->with('error', 'Permission not found.');
        }

        $allowedLevels = RolePermissionMatrix::allowedLevels($permission->name);

        return view('admin.permissions.show', compact('permission', 'allowedLevels'));
    }

    public function edit(int $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return redirect()->route('admin.permissions')->with('error', 'Permission not found.');
        }

        return view('admin.permissions.edit', [
            'permission' => $permission,
            'roleLevels' => RolePermissionMatrix::levels(),
            'allowedLevels' => RolePermissionMatrix::allowedLevels($permission->name),
            'tableOptions' => $this->tableOptions(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return redirect()->route('admin.permissions')->with('error', 'Permission not found.');
        }

        $validated = $this->validatePermission($request, $permission->id);

        $permission->update([
            'name' => $validated['name'],
            'table_name' => $validated['table_name'] ?? null,
        ]);

        return redirect()->route('admin.permissions.show', $permission->id)->with('success', 'Permission updated successfully.');
    }

    public function destroy(int $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return back()->with('error', 'Permission not found.');
        }

        if ($permission->roles()->exists()) {
            return back()->with('error', 'Permission cannot be deleted because it is assigned to one or more roles.');
        }

        $permission->deleted_by = Auth::id();
        $permission->save();
        $permission->delete();

        return redirect()->route('admin.permissions')->with('success', 'Permission deleted successfully.');
    }

    public function restore(int $id)
    {
        $permission = Permission::withTrashed()->find($id);

        if (!$permission) {
            return back()->with('error', 'Permission not found.');
        }

        if (is_null($permission->deleted_at)) {
            return back()->with('error', 'Permission is not deleted.');
        }

        $permission->restore();
        $permission->deleted_by = null;
        $permission->save();

        return redirect()->route('admin.permissions')->with('success', 'Permission restored successfully.');
    }

    private function validatePermission(Request $request, ?int $permissionId = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z][A-Za-z0-9]*_[A-Za-z][A-Za-z0-9]*$/',
                Rule::unique('permissions', 'name')->ignore($permissionId)->where(fn ($query) => $query->where('guard_name', 'web')),
            ],
            'table_name' => ['nullable', 'string', 'max:100'],
        ], [
            'name.regex' => 'Permission name must follow the Module_Action format, for example User_ViewAll.',
        ]);
    }

    private function actionOptions(): array
    {
        return [
            'Menu',
            'ViewAll',
            'View',
            'ViewMine',
            'Add',
            'Edit',
            'Delete',
            'Revoke',
        ];
    }

    private function excludedTables(): array
    {
        return [
            'migrations',
            'password_resets',
            'password_reset_tokens',
            'failed_jobs',
            'personal_access_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'user_activity_logs',
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
            'user_socket_connections',
        ];
    }

    private function tableOptions(): array
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . DB::getDatabaseName();

            return collect($tables)
                ->pluck($tableKey)
                ->filter(fn ($table) => !in_array($table, $this->excludedTables(), true))
                ->values()
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function exportPermissions($permissionsQuery, bool $isDeleted)
    {
        $permissions = $permissionsQuery->withCount([
            'roles as roles_count' => function ($query) {
                $query->where('roles.id', '!=', SystemVisibility::superAdminRoleId());
            },
        ])->get();

        $callback = function () use ($permissions, $isDeleted) {
            $file = fopen('php://output', 'w');

            $headers = ['ID', 'Name', 'Group', 'Table Name', 'Assigned Roles', 'Created At'];

            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }

            fputcsv($file, $headers);

            foreach ($permissions as $permission) {
                $row = [
                    $permission->id,
                    $permission->name,
                    Str::before($permission->name, '_'),
                    $permission->table_name,
                    $permission->roles_count ?? 0,
                    optional($permission->created_at)->format('Y-m-d H:i:s'),
                ];

                if ($isDeleted) {
                    $row[] = optional($permission->deleted_at)->format('Y-m-d H:i:s');
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=permissions.csv',
        ]);
    }
}
