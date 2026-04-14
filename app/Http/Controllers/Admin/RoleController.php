<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Support\RolePermissionMatrix;
use App\Support\SystemVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Role_ViewAll', ['only' => ['index']]);
        $this->middleware('permission:Role_ViewAll|Role_View', ['only' => ['show']]);
        $this->middleware('permission:Role_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Role_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Role_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:Role_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->boolean('is_export');

        $rolesQuery = $isDeleted
            ? Role::onlyTrashed()->with(['permissions', 'createdByUser', 'updatedByUser', 'deletedByUser'])
            : Role::with(['permissions', 'createdByUser', 'updatedByUser']);

        $rolesQuery->withCount([
                'users as users_count' => function ($query) {
                    SystemVisibility::hideSuperAdminUsers($query);
                },
            ]);

        SystemVisibility::hideSuperAdminRole($rolesQuery, 'roles.id')->orderByDesc('created_at');

        if ($search !== '') {
            $rolesQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('role_level', 'LIKE', "%{$search}%");
            });
        }

        if ($isExport) {
            return $this->exportRoles($rolesQuery, $isDeleted);
        }

        $roles = $rolesQuery->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();

            return response()->json([
                'status' => true,
                'message' => 'Roles fetched successfully.',
                'data' => [
                    'items' => $roles->getCollection()->map(function (Role $role) use ($authUser) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'role_level' => $role->role_level,
                            'role_level_label' => RolePermissionMatrix::label($role->role_level),
                            'permissions_count' => $role->permissions->count(),
                            'users_count' => $role->users_count ?? 0,
                            'deleted_at' => optional($role->deleted_at)->toDateTimeString(),
                            'created_at_human' => optional($role->created_at)->format('d M Y, h:i A'),
                            'show_url' => route('admin.roles.show', $role->id),
                            'edit_url' => route('admin.roles.edit', $role->id),
                            'delete_url' => route('admin.roles.delete', $role->id),
                            'restore_url' => route('admin.roles.restore', $role->id),
                            'permissions' => [
                                'can_view' => $authUser->can('Role_ViewAll') || $authUser->can('Role_View'),
                                'can_edit' => $authUser->can('Role_Edit'),
                                'can_delete' => $authUser->can('Role_Delete'),
                                'can_restore' => $authUser->can('Role_Revoke'),
                            ],
                        ];
                    })->values(),
                    'pagination' => [
                        'current_page' => $roles->currentPage(),
                        'last_page' => $roles->lastPage(),
                        'per_page' => $roles->perPage(),
                        'total' => $roles->total(),
                        'from' => $roles->firstItem(),
                        'to' => $roles->lastItem(),
                    ],
                ],
            ]);
        }

        return view('admin.roles.index');
    }

    public function create()
    {
        return view('admin.roles.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateRole($request);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'role_level' => $validated['role_level'],
            'created_by' => Auth::id(),
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.roles')->with('success', 'Role created successfully.');
    }

    public function show(int $id)
    {
        $role = Role::withTrashed()
            ->with(['permissions', 'createdByUser', 'updatedByUser', 'deletedByUser'])
            ->withCount('users')
            ->find($id);

        if (!$role || SystemVisibility::isProtectedRole($role)) {
            return redirect()->route('admin.roles')->with('error', 'Role not found.');
        }

        $permissionGroups = RolePermissionMatrix::groupedPermissions($role->permissions);

        return view('admin.roles.show', compact('role', 'permissionGroups'));
    }

    public function edit(int $id)
    {
        $role = Role::with('permissions')->find($id);

        if (!$role || SystemVisibility::isProtectedRole($role)) {
            return redirect()->route('admin.roles')->with('error', 'Role not found.');
        }

        return view('admin.roles.edit', array_merge($this->formData(), compact('role')));
    }

    public function update(Request $request, int $id)
    {
        $role = Role::with('permissions')->find($id);

        if (!$role || SystemVisibility::isProtectedRole($role)) {
            return redirect()->route('admin.roles')->with('error', 'Role not found.');
        }

        $validated = $this->validateRole($request, $role->id);

        $role->update([
            'name' => $validated['name'],
            'role_level' => $validated['role_level'],
            'updated_by' => Auth::id(),
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.roles.show', $role->id)->with('success', 'Role updated successfully.');
    }

    public function destroy(int $id)
    {
        $role = Role::find($id);

        if (!$role || SystemVisibility::isProtectedRole($role)) {
            return back()->with('error', 'Role not found.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Role cannot be deleted because it is assigned to one or more users.');
        }

        $role->deleted_by = Auth::id();
        $role->save();
        $role->delete();

        return redirect()->route('admin.roles')->with('success', 'Role deleted successfully.');
    }

    public function restore(int $id)
    {
        $role = Role::withTrashed()->find($id);

        if (!$role || SystemVisibility::isProtectedRole($role)) {
            return back()->with('error', 'Role not found.');
        }

        if (is_null($role->deleted_at)) {
            return back()->with('error', 'Role is not deleted.');
        }

        $role->restore();
        $role->deleted_by = null;
        $role->save();

        return redirect()->route('admin.roles')->with('success', 'Role restored successfully.');
    }

    private function formData(): array
    {
        $permissions = Permission::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return [
            'roleLevels' => RolePermissionMatrix::levels(),
            'permissionGroups' => RolePermissionMatrix::groupedPermissions($permissions),
        ];
    }

    private function validateRole(Request $request, ?int $roleId = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($roleId)->where(fn ($query) => $query->where('guard_name', 'web')),
            ],
            'role_level' => ['required', Rule::in(RolePermissionMatrix::levelOptions())],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where(fn ($query) => $query->where('guard_name', 'web'))],
        ]);

        $selectedPermissions = $validated['permissions'] ?? [];
        $allowedPermissions = RolePermissionMatrix::allowedPermissionNames(
            $validated['role_level'],
            Permission::query()->whereNull('deleted_at')->pluck('name')
        );

        $invalidPermissions = collect($selectedPermissions)
            ->reject(fn ($permissionName) => in_array($permissionName, $allowedPermissions, true))
            ->values();

        if ($invalidPermissions->isNotEmpty()) {
            throw ValidationException::withMessages([
                'permissions' => 'Selected permissions are not allowed for the chosen role level.',
            ]);
        }

        return $validated;
    }

    private function exportRoles($rolesQuery, bool $isDeleted)
    {
        $roles = $rolesQuery->withCount([
            'users as users_count' => function ($query) {
                SystemVisibility::hideSuperAdminUsers($query);
            },
        ])->get();

        $callback = function () use ($roles, $isDeleted) {
            $file = fopen('php://output', 'w');

            $headers = ['ID', 'Name', 'Role Level', 'Permissions Count', 'Users Count', 'Created At', 'Updated At'];

            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }

            fputcsv($file, $headers);

            foreach ($roles as $role) {
                $row = [
                    $role->id,
                    $role->name,
                    $role->role_level,
                    $role->permissions->count(),
                    $role->users_count ?? 0,
                    optional($role->created_at)->format('Y-m-d H:i:s'),
                    optional($role->updated_at)->format('Y-m-d H:i:s'),
                ];

                if ($isDeleted) {
                    $row[] = optional($role->deleted_at)->format('Y-m-d H:i:s');
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=roles.csv',
        ]);
    }
}
