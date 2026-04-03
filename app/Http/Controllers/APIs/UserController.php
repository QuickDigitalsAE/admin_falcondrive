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
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\ImageUrlTrait;

class UserController extends Controller
{
    use ImageUrlTrait;

    /**
     * Constructor to apply permission-based middlewar
     *     
     */
    function __construct()
    {
        $this->middleware('permission:User_ViewAll', ['only' => ['getUsers']]);
        $this->middleware('permission:User_ViewMine', ['only' => ['getMyUsers']]);
        $this->middleware('permission:User_Add', ['only' => ['postUser']]);
        $this->middleware('permission:User_View', ['only' => ['editUser']]);
        $this->middleware('permission:User_Edit', ['only' => ['updateUser','changePassword']]);
        $this->middleware('permission:User_Delete', ['only' => ['deleteUser']]);
        $this->middleware('permission:User_Revoke', ['only' => ['revokeUser']]);
    }

    public function getUsers()
    {
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');

        // Base query
        if ($is_deleted) {
            $usersQuery = User::onlyTrashed()->with('roles');
        } else {
            $usersQuery = User::with('roles');
        }

        // Filter by role ID = 2 for admins
        $usersQuery->whereHas('roles', function ($query) {
            $query->where('id', 2);
        });

        $usersQuery->where('id', '!=', 1)->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

         // === CSV Export ===
        if ($is_export) {
            $users = $usersQuery->get();

            $users->load(['createdByUser', 'updatedByUser']);
            // Preload created_by and updated_by users
            if ($is_deleted) {
                $users->load(['deletedByUser']);
            }
            
            $csvHeader = ['ID', 'Name', 'Email', 'Role', 'Status', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

             $callback = function () use ($users, $csvHeader, $is_deleted) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($users as $user) {
                    $row = [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->roles->pluck('name')->first(),
                        (int) $user->status,
                        optional($user->createdByUser)->name ?? 'N/A',
                        optional($user->updatedByUser)->name ?? 'N/A',
                        optional($user->created_at)->format('Y-m-d H:i:s'),
                        optional($user->updated_at)->format('Y-m-d H:i:s'),
                    ];

                    if ($is_deleted) {
                        $row[] = optional($user->deletedByUser)->name ?? 'N/A';
                        $row[] = optional($user->deleted_at)->format('Y-m-d H:i:s');
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
        $users = $usersQuery->paginate($per_page);
        
        $pagination = [
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total()
        ];

        if ($users->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No users found.',
                'data' => []
            ], 404);
        }

        $userList = $users->map(function ($user) {
            $profile_image = $user->profile_image ? $this->getImageUrl($user->profile_image) : null;
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => $profile_image,
                'role' => $user->roles->pluck('name')->first(),
                'status' => (int) $user->status
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'User list fetched successfully!',
            'data' => [
                'list' => $userList,
                'pagination' => $pagination
            ]
        ], 200);
    }

    public function getMyUsers()
    {
        $per_page = getPerPage(); // default to 10
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');
        $user_id = Auth::id();

        // Base query based on is_deleted
        if ($is_deleted) {
            $usersQuery = User::onlyTrashed()->with('roles');
        } else {
            $usersQuery = User::with('roles');
        }

        // Filter by role ID = 3 for users
        $usersQuery->whereHas('roles', function ($query) {
            $query->where('id', 2);
        });

        $usersQuery->where('id', '!=', 1)
            ->where('created_by', $user_id)
            ->orderBy('created_at', 'DESC');

        // Apply search filter
        if (!empty($search)) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // === CSV Export ===
        if ($is_export) {
            $users = $usersQuery->get();

            $users->load(['createdByUser', 'updatedByUser']);
            // Preload created_by and updated_by users
            if ($is_deleted) {
                $users->load(['deletedByUser']);
            }
            
            $csvHeader = ['ID', 'Name', 'Email', 'Role', 'Status', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

             $callback = function () use ($users, $csvHeader, $is_deleted) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($users as $user) {
                    $row = [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->roles->pluck('name')->first(),
                        (int) $user->status,
                        optional($user->createdByUser)->name ?? 'N/A',
                        optional($user->updatedByUser)->name ?? 'N/A',
                        optional($user->created_at)->format('Y-m-d H:i:s'),
                        optional($user->updated_at)->format('Y-m-d H:i:s'),
                    ];

                    if ($is_deleted) {
                        $row[] = optional($user->deletedByUser)->name ?? 'N/A';
                        $row[] = optional($user->deleted_at)->format('Y-m-d H:i:s');
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
        $users = $usersQuery->paginate($per_page);

        if ($users->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No users found.',
                'data' => []
            ], 404);
        }

        $userList = $users->map(function ($user) {
            $profile_image = $user->profile_image ? $this->getImageUrl($user->profile_image) : null;
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => $profile_image,
                'role' => $user->roles->pluck('name')->first(),
                'status' => (int) $user->status
            ];
        });

        $pagination = [
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total()
        ];

        return response()->json([
            'status' => true,
            'message' => 'User list fetched successfully!',
            'data' => [
                'list' => $userList,
                'pagination' => $pagination
            ]
        ], 200);
    }

    public function postUser(Request $request)
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
        $role = Role::find(2);

        if (!$role) {
            return response()->json([
                'status' => false,
                'message' => 'Role not found!',
                'data' => []
            ], 404);
        }
        
        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), 
            'status' => $request->status,
            'profile_image' => $profile_image,
            'created_by' => $authId
        ]);
        
        // Step 2: Assign the role to the user
        $user->assignRole($role);

        $roleId = $role->id ?? null;
        $roleName = $role->name ?? null;
        $profile_image = $user->profile_image ? $this->getImageUrl($user->profile_image) : null;

        // Return a response
        return response()->json([
            'status' => true,
            'message' => 'User added successfully.',
            'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => (int) $user->status,
                    'role_id' => $roleId,
                    'role_name' => $roleName,
                    'profile_image' => $profile_image
                ]
        ], Response::HTTP_CREATED); // HTTP 201 Created
    }

    public function editUser($id)
    {
        // Find the user by ID
        $user = User::with('roles')
                ->find($id);
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        // Ensure the user has role ID 2
        $hasRole2 = $user->roles->contains(function ($role) {
            return $role->id == 2;
        });

        if (!$hasRole2) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a Admin.',
                'data' => []
            ], Response::HTTP_FORBIDDEN);
        }

        $profile_image = $user->profile_image ? $this->getImageUrl($user->profile_image) : null;
        
        $roleController = new RoleController();
        $roles_fetch =  $roleController->getRoles();

        $role_list = null;
        if ($roles_fetch->original['data']) {
            $role_list = $roles_fetch->original['data']['list'] ?? null;
        }

        // Get user's assigned permissions
        $userPermissions = $user->getAllPermissions(); // Spatie method

        $permissions_grouped = $userPermissions->groupBy(function ($permission) {
            return Str::before($permission->name, '_');
        })->map(function ($group) {
            return $group->pluck('name')->values();
        });

        $role = $user->roles->first(); // Get first assigned role
        $roleId = $roleName = null;
        if ($role) {
            $roleId = $role->id;        // Role ID
            $roleName = $role->name;    // Role name
        }

        return response()->json([
            'status' => true,
            'message' => 'User details fetched successfully!',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => $profile_image,
                'role_id' => $roleId,
                'role_name' => $roleName,
                'status' => (int) $user->status,
                'roles' => $role_list,
                'permissions' => $permissions_grouped
            ]
        ], Response::HTTP_OK);
    }

    public function updateUser(Request $request, $id)
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

        $user_id = Auth::id();

        // Find the user with roles
        $user = User::with('roles')->find($id);
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found!',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        // Ensure the user has role ID 3
        if (!$user->roles->contains('id', 2)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a Admin.',
                'data' => []
            ], Response::HTTP_FORBIDDEN);
        }

        // Update user details
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
            
            // Invalidate all personal access tokens (logout from all devices)
            $user->tokens()->delete();
        }
        if ($request->has('status')) {
            $user->status = $request->status;
        }

        // Handle profile image upload
        $user->profile_image = $request->profile_image ?? null;
        $user->updated_by = $user_id;
        $user->save();

        $profile_image = $user->profile_image ? $this->getImageUrl($user->profile_image) : null;
        
        // Capture old role before syncing
        $role = $user->roles->first();
        $roleId = $roleName = null;
        if ($role) {
            $roleId = $role->id;        // Role ID
            $roleName = $role->name;    // Role name
        }
        
        return response()->json([
            'status' => true,
            'message' => 'User updated successfully!',
            'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => (int) $user->status,
                    'role_id' => $roleId,
                    'role_name' => $roleName,
                    'profile_image' => $profile_image
                ]
        ], Response::HTTP_OK);
    }

    public function changePassword(Request $request, $id)
    {
        // Validate input
        $validator = [
            'password' => 'required|string|min:6|confirmed',
        ];

        $request->validate($validator);

        // Find the user
        $user = User::with('roles')->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found!',
                'data' => []
            ], 404);
        }

        // Ensure the user has role ID 3
        if (!$user->roles->contains('id', 2)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a admin.',
                'data' => []
            ], 403);
        }

        $user_id = Auth::id();
        
        $user->updated_by = $user_id;
        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Invalidate all personal access tokens (logout from all devices)
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully.',
            'data' => []
        ], 200);
    }

    public function deleteUser($id)
    {
        $authId = Auth::id();

        $user = User::with('roles')
            ->where('id', '!=', 1)
            ->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found!',
                'data' => []
            ], 404);
        }

        // Ensure the user has role ID 2
        if (!$user->roles->contains('id', 2)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a admin!',
                'data' => []
            ], 403);
        }

        $user->deleted_by = $authId;
        $user->save();

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully.',
            'data' => []
        ], 200);
    }
    
    public function revokeUser($id)
    {
        // Find soft-deleted user
        $user = User::withTrashed()->with('roles')->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'user not found!',
                'data' => []
            ], 404);
        }

        // Ensure the user has role ID 3
        if (!$user->roles->contains('id', 3)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a admin!',
                'data' => []
            ], 403);
        }

        // Check if user is actually soft-deleted
        if (is_null($user->deleted_at)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not deleted.',
                'data' => []
            ], Response::HTTP_BAD_REQUEST);
        }

        // Restore the user
        $user->restore();
        $user->deleted_by = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User has been successfully restored.',
            'data' => []
        ], Response::HTTP_OK);
    }
    
}
