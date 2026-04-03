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
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Traits\ImageUrlTrait;

class EmployeeController extends Controller
{
    use ImageUrlTrait;

    /**
     * Constructor to apply permission-based middlewar
     *     
     */
    function __construct()
    {
        $this->middleware('permission:Employee_ViewAll', ['only' => ['getEmployees']]);
        $this->middleware('permission:Employee_ViewMine', ['only' => ['getMyemployees']]);
        $this->middleware('permission:Employee_View', ['only' => ['editEmployee']]);
        $this->middleware('permission:Employee_Add', ['only' => ['postEmployee']]);
        $this->middleware('permission:Employee_Edit', ['only' => ['updateEmployee','changePassword']]);
        $this->middleware('permission:Employee_Delete', ['only' => ['deleteEmployee']]);
        $this->middleware('permission:Employee_Revoke', ['only' => ['revokeEmployee']]);
    }

    public function getEmployees()
    {
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');

        // Base query
        if ($is_deleted) {
            $employeesQuery = User::onlyTrashed()->with('roles');
        } else {
            $employeesQuery = User::with('roles');
        }

        // Filter by role ID = 3 for employees
        $employeesQuery->whereHas('roles', function ($query) {
            $query->where('id', 4);
        });

        $employeesQuery->where('id', '!=', 1)->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $employeesQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

         // === CSV Export ===
        if ($is_export) {
            $employees = $employeesQuery->get();

            $employees->load(['createdByUser', 'updatedByUser']);
            // Preload created_by and updated_by users
            if ($is_deleted) {
                $employees->load(['deletedByUser']);
            }
            
            $csvHeader = ['ID', 'Name', 'Email', 'Role', 'Status', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

             $callback = function () use ($employees, $csvHeader, $is_deleted) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($employees as $employee) {
                    $row = [
                        $employee->id,
                        $employee->name,
                        $employee->email,
                        $employee->roles->pluck('name')->first(),
                        (int) $employee->status,
                        optional($employee->createdByUser)->name ?? 'N/A',
                        optional($employee->updatedByUser)->name ?? 'N/A',
                        optional($employee->created_at)->format('Y-m-d H:i:s'),
                        optional($employee->updated_at)->format('Y-m-d H:i:s'),
                    ];

                    if ($is_deleted) {
                        $row[] = optional($employee->deletedByUser)->name ?? 'N/A';
                        $row[] = optional($employee->deleted_at)->format('Y-m-d H:i:s');
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
        $employees = $employeesQuery->paginate($per_page);
        
        $pagination = [
            'current_page' => $employees->currentPage(),
            'last_page' => $employees->lastPage(),
            'per_page' => $employees->perPage(),
            'total' => $employees->total()
        ];

        if ($employees->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found!',
                'data' => []
            ], 404);
        }

        $employeeList = $employees->map(function ($employee) {
            $profile_image = $employee->profile_image ? $this->getImageUrl($employee->profile_image) : null;
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'profile_image' => $profile_image,
                'role' => $employee->roles->pluck('name')->first(),
                'status' => (int) $employee->status
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Employee list fetched successfully!',
            'data' => [
                'list' => $employeeList,
                'pagination' => $pagination
            ]
        ], 200);
    }

    public function getMyEmployees()
    {
        $per_page = getPerPage(); // default to 10
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');
        $employee_id = Auth::id();

        // Base query based on is_deleted
        if ($is_deleted) {
            $employeesQuery = User::onlyTrashed()->with('roles');
        } else {
            $employeesQuery = User::with('roles');
        }

        // Filter by role ID = 3 for employees
        $employeesQuery->whereHas('roles', function ($query) {
            $query->where('id', 4);
        });

        $employeesQuery->where('id', '!=', 1)
            ->where('created_by', $employee_id)
            ->orderBy('created_at', 'DESC');

        // Apply search filter
        if (!empty($search)) {
            $employeesQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // === CSV Export ===
        if ($is_export) {
            $employees = $employeesQuery->get();

            $employees->load(['createdByUser', 'updatedByUser']);
            // Preload created_by and updated_by users
            if ($is_deleted) {
                $employees->load(['deletedByUser']);
            }
            
            $csvHeader = ['ID', 'Name', 'Email', 'Role', 'Status', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

             $callback = function () use ($employees, $csvHeader, $is_deleted) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($employees as $employee) {
                    $row = [
                        $employee->id,
                        $employee->name,
                        $employee->email,
                        $employee->roles->pluck('name')->first(),
                        (int) $employee->status,
                        optional($employee->createdByUser)->name ?? 'N/A',
                        optional($employee->updatedByUser)->name ?? 'N/A',
                        optional($employee->created_at)->format('Y-m-d H:i:s'),
                        optional($employee->updated_at)->format('Y-m-d H:i:s'),
                    ];

                    if ($is_deleted) {
                        $row[] = optional($employee->deletedByUser)->name ?? 'N/A';
                        $row[] = optional($employee->deleted_at)->format('Y-m-d H:i:s');
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
        $employees = $employeesQuery->paginate($per_page);

        if ($employees->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found!',
                'data' => []
            ], 404);
        }

        $employeeList = $employees->map(function ($employee) {
            $profile_image = $employee->profile_image ? $this->getImageUrl($employee->profile_image) : null;
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'profile_image' => $profile_image,
                'role' => $employee->roles->pluck('name')->first(),
                'status' => (int) $employee->status
            ];
        });

        $pagination = [
            'current_page' => $employees->currentPage(),
            'last_page' => $employees->lastPage(),
            'per_page' => $employees->perPage(),
            'total' => $employees->total()
        ];

        return response()->json([
            'status' => true,
            'message' => 'Employee list fetched successfully!',
            'data' => [
                'list' => $employeeList,
                'pagination' => $pagination
            ]
        ], 200);
    }

    public function postEmployee(Request $request)
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

        $auth = Auth::user();
        $authId = $auth->id;

        // Fetch the role by role_id
        $role = Role::find(4);

        if (!$role) {
            return response()->json([
                'status' => false,
                'message' => 'Role not found!',
                'data' => []
            ], 404);
        }
      
        // Create the employee
        $employee = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), 
            'status' => $request->status,
            'profile_image' => $profile_image,
            'created_by' => $authId
        ]);
        
        // Step 2: Assign the role to the user
        $employee->assignRole($role);

        $roleId = $role->id ?? null;
        $roleName = $role->name ?? null;
        $profile_image = $employee->profile_image ? $this->getImageUrl($employee->profile_image) : null;

        // Return a response
        return response()->json([
            'status' => true,
            'message' => 'Employee added successfully.',
            'data' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'status' => (int) $employee->status,
                    'role_id' => $roleId,
                    'role_name' => $roleName,
                    'profile_image' => $profile_image
                ]
        ], Response::HTTP_CREATED); // HTTP 201 Created
    }

    public function editEmployee($id)
    {
        // Find the user with roles
        $employee = User::with('roles')->find($id);
        
        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found!',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        // Ensure the user has role ID 4
        $hasRole4 = $employee->roles->contains(function ($role) {
            return $role->id == 4;
        });

        if (!$hasRole4) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a employee.',
                'data' => []
            ], Response::HTTP_FORBIDDEN);
        }

        $profile_image = $employee->profile_image ? $this->getImageUrl($employee->profile_image) : null;

        // Get user's assigned permissions
        $employeePermissions = $employee->getAllPermissions(); // Spatie method

        $permissions_grouped = $employeePermissions->groupBy(function ($permission) {
            return Str::before($permission->name, '_');
        })->map(function ($group) {
            return $group->pluck('name')->values();
        });

        $role = $employee->roles->first();
        $roleId = $roleName = null;
        if ($role) {
            $roleId = $role->id;
            $roleName = $role->name;
        }

        return response()->json([
            'status' => true,
            'message' => 'employee details fetched successfully!',
            'data' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'profile_image' => $profile_image,
                'role_id' => $roleId,
                'role_name' => $roleName,
                'status' => (int) $employee->status,
                'permissions' => $permissions_grouped
            ]
        ], Response::HTTP_OK);
    }

    public function updateEmployee(Request $request, $id)
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

        $employee_id = Auth::id();

        // Find the user with roles
        $employee = User::with('roles')->find($id);
        
        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found!',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        // Ensure the user has role ID 3
        if (!$employee->roles->contains('id', 4)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a employee.',
                'data' => []
            ], Response::HTTP_FORBIDDEN);
        }

        // Update user details
        if ($request->has('name')) {
            $employee->name = $request->name;
        }
        if ($request->has('email')) {
            $employee->email = $request->email;
        }
        if ($request->has('password')) {
            $employee->password = Hash::make($request->password);
            
            // Invalidate all personal access tokens (logout from all devices)
            $employee->tokens()->delete();
        }
        if ($request->has('status')) {
            $employee->status = $request->status;
        }

        // Handle profile image upload
        $employee->profile_image = $request->profile_image ?? null;
        $employee->updated_by = $employee_id;
        $employee->save();

        $profile_image = $employee->profile_image ? $this->getImageUrl($employee->profile_image) : null;
        
        // Capture old role before syncing
        $role = $employee->roles->first();
        
        $roleId = $roleName = null;
        if ($role) {
            $roleId = $role->id;        // Role ID
            $roleName = $role->name;    // Role name
        }

        return response()->json([
            'status' => true,
            'message' => 'Employee updated successfully!',
            'data' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'status' => (int) $employee->status,
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

        $employee = User::with('roles')->find($id);

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found!',
                'data' => []
            ], 404);
        }

        // Ensure the user has role ID 3
        if (!$employee->roles->contains('id', 4)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a employee.',
                'data' => []
            ], Response::HTTP_FORBIDDEN);
        }

        $employee_id = Auth::id();

        $employee->updated_by = $employee_id;
        $employee->password = Hash::make($request->password);
        $employee->save();

        if (method_exists($employee, 'tokens')) {
            $employee->tokens()->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully.',
            'data' => []
        ], 200);
    }

    public function deleteEmployee($id)
    {
        $authId = Auth::id();

        $employee = User::with('roles')
            ->where('id', '!=', 1)
            ->find($id);

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found!',
                'data' => []
            ], 404);
        }

        // Ensure the user has role ID 3
        if (!$employee->roles->contains('id', 4)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a employee.',
                'data' => []
            ], Response::HTTP_FORBIDDEN);
        }

        $employee->deleted_by = $authId;
        $employee->save();

        $employee->delete();

        return response()->json([
            'status' => true,
            'message' => 'Employee deleted successfully.',
            'data' => []
        ], 200);
    }
    
    public function revokeEmployee($id)
    {
        $employee = User::withTrashed()->with('roles')->find($id);

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found!',
                'data' => []
            ], 404);
        }

        // Ensure the user has role ID 3
        if (!$employee->roles->contains('id', 4)) {
            return response()->json([
                'status' => false,
                'message' => 'User is not a employee.',
                'data' => []
            ], Response::HTTP_FORBIDDEN);
        }

        if (is_null($employee->deleted_at)) {
            return response()->json([
                'status' => false,
                'message' => 'Employee is not deleted.',
                'data' => []
            ], 400);
        }

        $employee->restore();
        $employee->deleted_by = null;
        $employee->save();

        return response()->json([
            'status' => true,
            'message' => 'Employee has been successfully restored.',
            'data' => []
        ], 200);
    }

}
