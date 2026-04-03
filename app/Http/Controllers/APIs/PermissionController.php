<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Permission;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Constructor to apply permission-based middlewar
     *     
     */
    function __construct()
    {
        $this->middleware('permission:Permissions_ViewAll', ['only' => ['getPermissions']]);
        $this->middleware('permission:Permissions_Add', ['only' => ['postPermission']]);
        $this->middleware('permission:Permissions_Edit', ['only' => ['updatePermission']]);
        $this->middleware('permission:Permissions_Delete', ['only' => ['deletePermissions']]);
        $this->middleware('permission:Permissions_Revoke', ['only' => ['revokePermissions']]);
    }

    public function postPermission(Request $request)
    {
        $validator = [
            'permissions' => 'required|array'
        ];

        $request->validate($validator);

        $permissionGroup = $request->permissions;

        foreach ($permissionGroup as $permission) {
            // Each item should be an array like ['name' => 'User_View', 'table_name' => 'users']
            Permission::firstOrCreate([
                'name' => $permission['name'],
                'guard_name' => 'sanctum',
                'table_name' => $permission['table_name'] ?? null,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Permissions added successfully',
        ], Response::HTTP_CREATED);
    }

    public function getPermissions(Request $request)
    {
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $permissionsQuery = $is_deleted ? Permission::onlyTrashed() : Permission::query();

        if (!empty($search)) {
            $permissionsQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('table_name', 'LIKE', "%{$search}%");
            });
        }

        $permissionsQuery->orderBy('created_at', 'DESC');

        if ($is_export) {
            $permissions = $permissionsQuery->get();

            $csvHeader = ['ID', 'Name', 'Group', 'Table Name', 'Created At'];
            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($permissions, $csvHeader, $is_deleted) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($permissions as $permission) {
                    $row = [
                        $permission->id,
                        $permission->name,
                        Str::before($permission->name, '_'),
                        $permission->table_name ?? '',
                        optional($permission->created_at)->format('Y-m-d H:i:s')
                    ];

                    if ($is_deleted) {
                        $deletedBy = optional(User::find($permission->deleted_by))->name;
                        $deletedAt = optional($permission->deleted_at)->format('Y-m-d H:i:s');
                        $row[] = $deletedBy;
                        $row[] = $deletedAt;
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };

            $fileName = 'permissions_export_' . now()->format('Ymd_His') . '.csv';

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$fileName}",
            ]);
        }

        $permissions = $permissionsQuery->paginate($per_page);

        if ($permissions->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No permissions found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $groupedPermissions = $permissions->getCollection()
            ->groupBy(function ($permission) {
                return Str::before($permission->name, '_');
            })
            ->map(function ($group) {
                return $group->values()->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'group' => Str::before($permission->name, '_'),
                        'table_name' => $permission->table_name,
                    ];
                });
            });

        $pagination = [
            'current_page' => $permissions->currentPage(),
            'last_page' => $permissions->lastPage(),
            'per_page' => $permissions->perPage(),
            'total' => $permissions->total()
        ];

        return response()->json([
            'status' => true,
            'message' => 'Permissions fetched successfully!',
            'data' => [
                'list' => $groupedPermissions,
                'pagination' => $pagination
            ]
        ], Response::HTTP_OK);
    }

    public function updatePermission(Request $request, $id)
    {
        $validator = [
            'name'       => 'required|string|unique:permissions,name,' . $id,
            'table_name' => 'nullable|string'
        ];

        $request->validate($validator);

        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'status' => false,
                'message' => 'Permission not found.'
            ], 404);
        }

        $permission->name = $request->input('name');
        $permission->table_name = $request->input('table_name'); // optional update

        $permission->save();

        return response()->json([
            'status' => true,
            'message' => 'Permission updated successfully.'
        ], 200);
    }
    
    public function deletePermissions($group)
    {
        if (!$group || !is_string($group)) {
            return response()->json([
                'status' => false,
                'message' => 'Group is required and must be a valid string.'
            ], 400);
        }

        $authId = Auth::id();

        // Find all permissions where name starts with "{group}_"
        $permissions = Permission::where('name', 'like', $group . '_%')->get();

        if ($permissions->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No permissions found for the specified group.'
            ], 404);
        }

        foreach ($permissions as $permission) {

            if ($permission->roles()->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'One or more permissions in this group are assigned to roles. Cannot delete.'
                ], 403);
            }

            $permission->deleted_by = $authId;
            $permission->save();
            
            $permission->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'All permissions under the group "' . $group . '" deleted successfully.'
        ]);
    }

    public function revokePermissions($group)
    {
        if (!$group || !is_string($group)) {
            return response()->json([
                'status' => false,
                'message' => 'Group is required and must be a valid string.'
            ], 400);
        }

        // Find soft-deleted permissions where name starts with "{group}_"
        $permissions = Permission::withTrashed()
            ->where('name', 'like', $group . '_%')
            ->whereNotNull('deleted_at')
            ->get();

        if ($permissions->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No soft-deleted permissions found for the specified group.'
            ], 404);
        }

        foreach ($permissions as $permission) {
            $permission->restore(); // Restore the soft-deleted permission
            $permission->deleted_by = null; // Clear deleted_by info
            $permission->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'All permissions under the group "' . $group . '" have been restored successfully.'
        ],Response::HTTP_OK);
    }

    public function getAllTables()
    {
        $tables = DB::select('SHOW TABLES');

        // Get the key name for the table column (e.g., "Tables_in_yourdbname")
        $tableKey = 'Tables_in_' . DB::getDatabaseName();

        $tableNames = collect($tables)->pluck($tableKey);

        return response()->json([
            'status' => true,
            'message' => 'Table list fetched successfully.',
            'data' => $tableNames
        ],200);
    }

}
