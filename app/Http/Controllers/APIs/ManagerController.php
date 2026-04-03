<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Http\Controllers\APIs\RoleController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use App\Traits\ImageUrlTrait;

class ManagerController extends Controller
{
    use ImageUrlTrait;

    /**
     * Constructor to apply permission-based middlewar
     *     
     */
    function __construct()
    {
        $this->middleware('permission:Manager_ViewAll', ['only' => ['getManagers']]);
        $this->middleware('permission:Manager_ViewMine', ['only' => ['getMyManagers']]);
        $this->middleware('permission:Manager_Add', ['only' => ['postManager']]);
        $this->middleware('permission:Manager_View', ['only' => ['editManager']]);
        $this->middleware('permission:Manager_Edit', ['only' => ['updateManager','changePassword']]);
        $this->middleware('permission:Manager_Delete', ['only' => ['deleteManager']]);
        $this->middleware('permission:Manager_Revoke', ['only' => ['revokeManager']]);
    }

    public function getManagers()
    {
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');

        // Base query
        if ($is_deleted) {
            $managersQuery = User::onlyTrashed()->with('roles');
        } else {
            $managersQuery = User::with('roles');
        }

        // Filter by role ID = 3 for managers
        $managersQuery->whereHas('roles', function ($query) {
            $query->where('id', 3);
        });

        $managersQuery->where('id', '!=', 1)->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $managersQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

         // === CSV Export ===
        if ($is_export) {
            $managers = $managersQuery->get();

            $managers->load(['createdByUser', 'updatedByUser']);
            // Preload created_by and updated_by users
            if ($is_deleted) {
                $managers->load(['deletedByUser']);
            }
            
            $csvHeader = ['ID', 'Name', 'Email', 'Role', 'Status', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

             $callback = function () use ($managers, $csvHeader, $is_deleted) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($managers as $manager) {
                    $row = [
                        $manager->id,
                        $manager->name,
                        $manager->email,
                        $manager->roles->pluck('name')->first(),
                        (int) $manager->status,
                        optional($manager->createdByUser)->name ?? 'N/A',
                        optional($manager->updatedByUser)->name ?? 'N/A',
                        optional($manager->created_at)->format('Y-m-d H:i:s'),
                        optional($manager->updated_at)->format('Y-m-d H:i:s'),
                    ];

                    if ($is_deleted) {
                        $row[] = optional($manager->deletedByUser)->name ?? 'N/A';
                        $row[] = optional($manager->deleted_at)->format('Y-m-d H:i:s');
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };


            $fileName = 'my_users_export_' . now()->format('Ymd_His') . '.csv';

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$fileName}",
            ]);
        }

        // Normal paginated response
        $managers = $managersQuery->paginate($per_page);
        
        $pagination = [
            'current_page' => $managers->currentPage(),
            'last_page' => $managers->lastPage(),
            'per_page' => $managers->perPage(),
            'total' => $managers->total()
        ];

        if ($managers->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Manager not found!',
                'data' => []
            ], 404);
        }

        $managerList = $managers->map(function ($manager) {
            $profile_image = $manager->profile_image ? $this->getImageUrl($manager->profile_image) : null;
            return [
                'id' => $manager->id,
                'name' => $manager->name,
                'email' => $manager->email,
                'profile_image' => $profile_image,
                'role' => $manager->roles->pluck('name')->first(),
                'status' => (int) $manager->status
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Manager list fetched successfully!',
            'data' => [
                'list' => $managerList,
                'pagination' => $pagination
            ]
        ], 200);
    }

    public function getMyManagers()
    {
        $per_page = getPerPage(); // default to 10
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');
        $manager_id = Auth::id();

        // Base query based on is_deleted
        if ($is_deleted) {
            $managersQuery = User::onlyTrashed()->with('roles');
        } else {
            $managersQuery = User::with('roles');
        }

        // Filter by role ID = 3 for managers
        $managersQuery->whereHas('roles', function ($query) {
            $query->where('id', 3);
        });

        $managersQuery->where('id', '!=', 1)
            ->where('created_by', $manager_id)
            ->orderBy('created_at', 'DESC');

        // Apply search filter
        if (!empty($search)) {
            $managersQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // === CSV Export ===
        if ($is_export) {
            $managers = $managersQuery->get();

            $managers->load(['createdByUser', 'updatedByUser']);
            // Preload created_by and updated_by users
            if ($is_deleted) {
                $managers->load(['deletedByUser']);
            }
            
            $csvHeader = ['ID', 'Name', 'Email', 'Role', 'Status', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

             $callback = function () use ($managers, $csvHeader, $is_deleted) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($managers as $manager) {
                    $row = [
                        $manager->id,
                        $manager->name,
                        $manager->email,
                        $manager->roles->pluck('name')->first(),
                        (int) $manager->status,
                        optional($manager->createdByUser)->name ?? 'N/A',
                        optional($manager->updatedByUser)->name ?? 'N/A',
                        optional($manager->created_at)->format('Y-m-d H:i:s'),
                        optional($manager->updated_at)->format('Y-m-d H:i:s'),
                    ];

                    if ($is_deleted) {
                        $row[] = optional($manager->deletedByUser)->name ?? 'N/A';
                        $row[] = optional($manager->deleted_at)->format('Y-m-d H:i:s');
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };


            $fileName = 'my_users_export_' . now()->format('Ymd_His') . '.csv';

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$fileName}",
            ]);
        }

        // Normal paginated response
        $managers = $managersQuery->paginate($per_page);

        if ($managers->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Manager not found!',
                'data' => []
            ], 404);
        }

        $managerList = $managers->map(function ($manager) {
            $profile_image = $manager->profile_image ? $this->getImageUrl($manager->profile_image) : null;
            return [
                'id' => $manager->id,
                'name' => $manager->name,
                'email' => $manager->email,
                'profile_image' => $profile_image,
                'role' => $manager->roles->pluck('name')->first(),
                'status' => (int) $manager->status
            ];
        });

        $pagination = [
            'current_page' => $managers->currentPage(),
            'last_page' => $managers->lastPage(),
            'per_page' => $managers->perPage(),
            'total' => $managers->total()
        ];

        return response()->json([
            'status' => true,
            'message' => 'Manager list fetched successfully!',
            'data' => [
                'list' => $managerList,
                'pagination' => $pagination
            ]
        ], 200);
    }

    public function postManager(Request $request)
    {
        // Validate the request
        $validator = [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed', // Ensure password confirmation
            'status' => 'required|numeric',
            'profile_image' => 'nullable|string',
        ];
        
        $request->validate($validator);

        // Handle profile image upload
        $profile_image = $request->profile_image ?? null;

        $authId = Auth::id();
        
        // Fetch the role by role_id
        $role = Role::find(3);

        if (!$role) {
            return response()->json([
                'status' => false,
                'message' => 'Role not found!',
                'data' => []
            ], 404);
        }

        // Create the manager
        $manager = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), 
            'status' => $request->status,
            'profile_image' => $profile_image,
            'created_by' => $authId
        ]);
        
        // Step 2: Assign the role to the user
        $manager->assignRole($role);

        $roleId = $role->id ?? null;
        $roleName = $role->name ?? null;
        $profile_image = $manager->profile_image ? $this->getImageUrl($manager->profile_image) : null;

        // Return a response
        return response()->json([
            'status' => true,
            'message' => 'Manager added successfully.',
            'data' => [
                    'id' => $manager->id,
                    'name' => $manager->name,
                    'email' => $manager->email,
                    'status' => (int) $manager->status,
                    'role_id' => $roleId,
                    'role_name' => $roleName,
                    'profile_image' => $profile_image
                ]
        ], Response::HTTP_CREATED); // HTTP 201 Created
    }

    public function editManager($id)
    {
        // Find the user with roles
        $manager = User::with('roles')->find($id);
        
        if (!$manager) {
            return response()->json([
                'status' => false,
                'message' => 'Manager not found!',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        // Ensure the user has role ID 3
        $hasRole3 = $manager->roles->contains(function ($role) {
            return $role->id == 3;
        });

        if (!$hasRole3) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a Manager.',
                'data' => []
            ], Response::HTTP_FORBIDDEN);
        }

        $profile_image = $manager->profile_image ? $this->getImageUrl($manager->profile_image) : null;

        // Get user's assigned permissions
        $managerPermissions = $manager->getAllPermissions(); // Spatie method

        $permissions_grouped = $managerPermissions->groupBy(function ($permission) {
            return Str::before($permission->name, '_');
        })->map(function ($group) {
            return $group->pluck('name')->values();
        });

        $role = $manager->roles->first();
        $roleId = $roleName = null;
        if ($role) {
            $roleId = $role->id;
            $roleName = $role->name;
        }

        return response()->json([
            'status' => true,
            'message' => 'Manager details fetched successfully!',
            'data' => [
                'id' => $manager->id,
                'name' => $manager->name,
                'email' => $manager->email,
                'profile_image' => $profile_image,
                'role_id' => $roleId,
                'role_name' => $roleName,
                'status' => (int) $manager->status,
                'permissions' => $permissions_grouped
            ]
        ], Response::HTTP_OK);
    }

    public function updateManager(Request $request, $id)
    {
        // Validate the request
        $validator = [
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|string|email|unique:users,email,' . $id, // Exclude current user email from unique check
            'password' => 'sometimes|nullable|string|confirmed',
            'status' => 'sometimes|numeric',
            'profile_image' => 'nullable|string',
        ];
        
        $request->validate($validator);

        $manager_id = Auth::id();

        // Find the user with roles
        $manager = User::with('roles')->find($id);
        
        if (!$manager) {
            return response()->json(["status" => false, 'message' => 'Manager not found!'], Response::HTTP_NOT_FOUND);
        }

        // Ensure the user has role ID 3
        if (!$manager->roles->contains('id', 3)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a manager.',
                'data' => []
            ], Response::HTTP_FORBIDDEN);
        }

        // Update user details
        if ($request->has('name')) {
            $manager->name = $request->name;
        }
        if ($request->has('email')) {
            $manager->email = $request->email;
        }
        if ($request->has('password')) {
            $manager->password = Hash::make($request->password);
            
            // Invalidate all personal access tokens (logout from all devices)
            $manager->tokens()->delete();
        }
        if ($request->has('status')) {
            $manager->status = $request->status;
        }

        // Handle profile image upload
        $manager->profile_image = $request->profile_image ?? null;
        $manager->updated_by = $manager_id;
        $manager->save();

        $profile_image = $manager->profile_image ? $this->getImageUrl($manager->profile_image) : null;
        
        // Capture old role before syncing
        $role = $manager->roles->first();
        
        $roleId = $roleName = null;
        if ($role) {
            $roleId = $role->id;        // Role ID
            $roleName = $role->name;    // Role name
        }

        return response()->json([
            'status' => true,
            'message' => 'Manager updated successfully!',
            'data' => [
                    'id' => $manager->id,
                    'name' => $manager->name,
                    'email' => $manager->email,
                    'status' => (int) $manager->status,
                    'role_id' => $roleId,
                    'role_name' => $roleName,
                    'profile_image' => $profile_image
                ]
        ], Response::HTTP_OK);
    }

    public function changePassword(Request $request, $id)
    {
        $validator = [
            'password' => 'required|string|min:6|confirmed',
        ];

        $request->validate($validator);

        $manager = User::with('roles')->find($id);

        if (!$manager) {
            return response()->json([
                'status' => false,
                'message' => 'Manager not found!.',
                'data' => []
            ], 404);
        }

        // Ensure the user has role ID 3
        if (!$manager->roles->contains('id', 3)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a manager.',
                'data' => []
            ], 403);
        }

        $manager_id = Auth::id();

        $manager->updated_by = $manager_id;
        $manager->password = Hash::make($request->password);
        $manager->save();

        if (method_exists($manager, 'tokens')) {
            $manager->tokens()->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully.',
            'data' => []
        ], 200);
    }

    public function deleteManager($id)
    {
        $authId = Auth::id();

        $manager = User::with('roles')
            ->where('id', '!=', 1)
            ->find($id);

        if (!$manager) {
            return response()->json([
                'status' => false,
                'message' => 'Manager not found!',
                'data' => []
            ], 404);
        }

        // Ensure the user has role ID 3
        if (!$manager->roles->contains('id', 3)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a manager.',
                'data' => []
            ], 403);
        }

        $manager->deleted_by = $authId;
        $manager->save();

        $manager->delete();

        return response()->json([
            'status' => true,
            'message' => 'Manager deleted successfully.',
            'data' => []
        ], 200);
    }
    
    public function revokeManager($id)
    {
        $manager = User::withTrashed()->with('roles')->find($id);

        if (!$manager) {
            return response()->json([
                'status' => false,
                'message' => 'Manager not found!.',
                'data' => []
            ], 404);
        }

        // Ensure the user has role ID 3
        if (!$manager->roles->contains('id', 3)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a manager.',
                'data' => []
            ], 403);
        }

        if (is_null($manager->deleted_at)) {
            return response()->json([
                'status' => false,
                'message' => 'Manager is not deleted.',
                'data' => []
            ], 400);
        }

        $manager->restore();
        $manager->deleted_by = null;
        $manager->save();

        return response()->json([
            'status' => true,
            'message' => 'Manager has been successfully restored.',
            'data' => []
        ], 200);
    }

}
