<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\Permission;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * Constructor to apply permission-based middlewar
     *     
     */
    function __construct()
    {
        $this->middleware('permission:Role_ViewAll', ['only' => ['getRoles']]);
        $this->middleware('permission:Role_View', ['only' => ['editRole']]);
        $this->middleware('permission:Role_Add', ['only' => ['postRole']]);
        $this->middleware('permission:Role_Edit', ['only' => ['updateRole']]);
        $this->middleware('permission:Role_Delete', ['only' => ['deleteRole']]);
        $this->middleware('permission:Role_Revoke', ['only' => ['revokeRole']]);
    }
    
    public function getRoles()
    {
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');

        // Base query
        if ($is_deleted) {
            $rolesQuery = Role::onlyTrashed();
        } else {
            $rolesQuery = Role::query();
        }

        // Exclude super admin
        $rolesQuery->where('id', '!=', 1)->orderBy('created_at', 'DESC');

        // Search filter
        if (!empty($search)) {
            $rolesQuery->where('name', 'LIKE', "%{$search}%");
        }

        // === CSV EXPORT ===
        if ($is_export) {
            $roles = $rolesQuery->get();

            // Preload related user data (assuming relationships exist)
            $roles->load(['createdByUser', 'updatedByUser']);
            if ($is_deleted) {
                $roles->load(['deletedByUser']);
            }

            $csvHeader = [
                'ID', 'Name',
                'Created By', 'Created At',
                'Updated By', 'Updated At'
            ];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($roles, $csvHeader, $is_deleted) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($roles as $role) {
                    $row = [
                        $role->id,
                        $role->name,
                        optional($role->createdByUser)->name,
                        optional($role->created_at)->format('Y-m-d H:i:s'),
                        optional($role->updatedByUser)->name,
                        optional($role->updated_at)->format('Y-m-d H:i:s')
                    ];

                    if ($is_deleted) {
                        $row[] = optional($role->deletedByUser)->name;
                        $row[] = optional($role->deleted_at)->format('Y-m-d H:i:s');
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };

            $fileName = 'roles_export_' . now()->format('Ymd_His') . '.csv';

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$fileName}",
            ]);
        }

        // === Paginated JSON Response ===
        $roles = $rolesQuery->paginate($per_page);

        if ($roles->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No roles found!'
            ], Response::HTTP_NOT_FOUND);
        }

        $roleList = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
            ];
        });

        $pagination = [
            'current_page' => $roles->currentPage(),
            'last_page' => $roles->lastPage(),
            'per_page' => $roles->perPage(),
            'total' => $roles->total()
        ];

        return response()->json([
            'status' => true,
            'message' => 'Roles fetched successfully!',
            'data' => [
                'list' => $roleList,
                'pagination' => $pagination
            ]
        ], Response::HTTP_OK);
    }
    
    public function postRole(Request $request)
    {
        // Validate the request
        $validator = [
            'role_name' => 'required|string|max:255',
            'permissions' => 'array', // should be an array of permission names
        ];

        $request->validate($validator);

        try {
            $authId = Auth::id();

            // Create a new role
            $role = Role::create([
                        'name' => $request['role_name'],
                        'guard_name' => 'sanctum',
                        'created_by' => $authId
                    ]);

            // Re-assign based on permissionAll or permissions input
            if ($request->has('permissionAll') && $request->permissionAll == 1) {
                $allPermissions = Permission::pluck('name')->toArray();
                $role->syncPermissions($allPermissions);
            } elseif (!empty($request->permissions)) {
                $role->syncPermissions($request->permissions);
            }else{
                // Remove all existing permissions
                $role->syncPermissions([]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Role created successfully',
                'data' => $role
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong!'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function editRole($roleId)
    {
        // Find the role by ID
        $role = Role::find($roleId);

        // Check if the role exists
        if (!$role) {
            return response()->json([
                "status" => false,
                'message' => 'Role not found!'
            ], Response::HTTP_NOT_FOUND);
        }

        // Assigned permissions of the role (formatted)
        $rolePermissions = $role->permissions->map(function ($permission) {
            return [
                'id'    => $permission->id,
                'name'  => $permission->name,
                'group' => Str::before($permission->name, '_')
            ];
        });

        // Inject formatted permissions into role array
        $roleData = $role->toArray();
        $roleData['permissions'] = $rolePermissions;

        // Get the role's permissions grouped by `group`
        $permissions = Permission::all()
            ->groupBy(function ($permission) {
                return Str::before($permission->name, '_'); // Group key e.g. "User"
            })
            ->map(function ($group) {
                return $group->values()->map(function ($permission) {
                    return [
                        'id'          => $permission->id,
                        'name'        => $permission->name,
                        'group'       => Str::before($permission->name, '_')
                    ];
                });
            });   

        // Return the role and its permissions grouped by `group`
        return response()->json([
            "status" => true,
            "message" => 'Role fetched successfully!',
            "data" => [
                'role' => $roleData,
                'get_all_permissions' => $permissions
            ]
        ], Response::HTTP_OK);
    }

    public function updateRole(Request $request, $roleId)
    {
        // Validate the request
        $validator = [
            'role_name' => 'required|string',
            'permissions' => 'array', // should be an array of permission names
        ];

        $request->validate($validator);

        $authId = Auth::id();

        // Find the role by ID
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json([
                "status" => false,
                'message' => 'Role not found!'
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if the new role name already exists
        $existsRole = Role::where('name', $request->role_name)
            ->where('guard_name', 'sanctum')
            ->where('id', '!=', $roleId) // Exclude the current role from the check
            ->exists();

        if ($existsRole) {
            return response()->json([
                'status' => false,
                'message' => 'Role already exists'
            ], Response::HTTP_OK);
        }

        // Update the role's name
        $role->name = $request->role_name;
        $role->updated_by = $authId;
        $role->save();

        // Re-assign based on permissionAll or permissions input
        if ($request->has('permissionAll') && $request->permissionAll == 1) {
            $allPermissions = Permission::pluck('name')->toArray();
            $role->syncPermissions($allPermissions);
        } elseif (!empty($request->permissions)) {
            $role->syncPermissions($request->permissions);
        }else{
            // Remove all existing permissions
            $role->syncPermissions([]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Role updated successfully'
        ], Response::HTTP_OK);
    }

    public function deleteRole($roleId)
    {
        // Find the role by ID
        $role = Role::find($roleId);

        // Check if the role exists
        if ($role) {
            if ($role->id == 1) {
                return response()->json([
                    "status" => false,
                    'message' => 'The Super Admin role cannot be deleted.'
                ], Response::HTTP_UNAUTHORIZED); // HTTP status 403 for forbidden action
            }
        }else{
            return response()->json([
                "status" => false,
                'message' => 'Role not found!'
            ], Response::HTTP_NOT_FOUND); // Return 404 Not Found if the role does not exist
        }

        if ($role->users()->exists()) { // This checks if any users are associated with the role
            return response()->json([
                "status" => false,
                'message' => 'Role cannot be deleted because it is assigned to one or more users.'
            ], Response::HTTP_UNAUTHORIZED); // Return 409 Conflict if the role is assigned
        }
        $authId = Auth::id();
        $role->deleted_by = $authId;
        $role->save();

        // Delete the role (this will also detach the associated permissions)
        $role->delete();

        return response()->json([
            "status" => true,
            'message' => 'Role deleted successfully!'
        ], Response::HTTP_OK);
    }

    public function revokeRole($id)
    {
        // Find soft-deleted role
        $role = Role::withTrashed()->find($id);

        if (!$role) {
            return response()->json([
                'status' => false,
                'message' => 'Role not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if user is actually soft-deleted
        if (is_null($role->deleted_at)) {
            return response()->json([
                'status' => false,
                'message' => 'Role is not deleted.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Restore the user
        $role->restore();
        $role->deleted_by = null;
        $role->save();

        return response()->json([
            'status' => true,
            'message' => 'Role has been successfully restored.'
        ], Response::HTTP_OK);
    }
}
