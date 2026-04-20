<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\SystemVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:User_ViewAll|User_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:User_ViewAll|User_ViewMine|User_View', ['only' => ['showUser']]);
        $this->middleware('permission:User_Add', ['only' => ['createUser', 'postUser']]);
        $this->middleware('permission:User_Edit', ['only' => ['editUser', 'updateUser', 'changePassword']]);
        $this->middleware('permission:User_Delete', ['only' => ['deleteUser']]);
        $this->middleware('permission:User_Revoke', ['only' => ['revokeUser']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('User_ViewAll')) {
            return $this->getUsers($request);
        }

        if ($user->can('User_ViewMine')) {
            return $this->getMyUsers($request);
        }

        abort(403, 'You do not have permission to view users.');
    }

    public function getUsers(Request $request)
    {
        $per_page = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $is_deleted = $request->boolean('is_deleted');
        $is_export = $request->query('is_export');

        if ($is_deleted) {
            $usersQuery = User::onlyTrashed()->with(['roles', 'createdByUser', 'updatedByUser', 'deletedByUser']);
        } else {
            $usersQuery = User::with(['roles', 'createdByUser', 'updatedByUser']);
        }

        SystemVisibility::hideSuperAdminUsers($usersQuery)->orderByDesc('created_at');

        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('emp_id', 'LIKE', "%{$search}%")
                    ->orWhere('cnic', 'LIKE', "%{$search}%")
                    ->orWhere('passport', 'LIKE', "%{$search}%");
            });
        }

        if ($is_export) {
            return $this->exportUsers($usersQuery, $is_deleted);
        }

        $users = $usersQuery->paginate($per_page)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $list = $users->getCollection()->map(function ($user) {
                $authUser = Auth::user();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_image_url' => $user->profile_image_url,
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                        ];
                    })->values(),
                    'status' => (int) $user->status,
                    'deleted_at' => $user->deleted_at ? $user->deleted_at->toDateTimeString() : null,
                    'created_at_human' => optional($user->created_at)->format('d M Y, h:i A'),

                    ...$this->superAdminAuditMeta($user, $authUser),
                    'show_url' => route('admin.users.show', $user->id),
                    'edit_url' => route('admin.users.edit', $user->id),
                    'delete_url' => route('admin.users.delete', $user->id),
                    'restore_url' => route('admin.users.revoke', $user->id),

                    'permissions' => [
                        'can_view' => $authUser->can('User_ViewAll') || $authUser->can('User_ViewMine') || $authUser->can('User_View'),
                        'can_edit' => $authUser->can('User_Edit'),
                        'can_delete' => $authUser->can('User_Delete'),
                        'can_restore' => $authUser->can('User_Revoke'),
                    ],
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Users fetched successfully.',
                'data' => [
                    'items' => $list,
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                        'from' => $users->firstItem(),
                        'to' => $users->lastItem(),
                        'has_more_pages' => $users->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $is_deleted,
                    ],
                ],
            ]);
        }

        return view('admin.users.index', compact('users'));
    }

    public function getMyUsers(Request $request)
    {
        $per_page = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $is_deleted = $request->boolean('is_deleted');
        $is_export = $request->query('is_export');
        $user_id = Auth::id();

        if ($is_deleted) {
            $usersQuery = User::onlyTrashed()->with(['roles', 'createdByUser', 'updatedByUser', 'deletedByUser']);
        } else {
            $usersQuery = User::with(['roles', 'createdByUser', 'updatedByUser']);
        }

        SystemVisibility::hideSuperAdminUsers($usersQuery);
        $usersQuery->where('created_by', $user_id)->orderByDesc('created_at');

        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('emp_id', 'LIKE', "%{$search}%")
                    ->orWhere('cnic', 'LIKE', "%{$search}%")
                    ->orWhere('passport', 'LIKE', "%{$search}%");
            });
        }

        if ($is_export) {
            return $this->exportUsers($usersQuery, $is_deleted);
        }

        $users = $usersQuery->paginate($per_page)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $list = $users->getCollection()->map(function ($user) {
                $authUser = Auth::user();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_image_url' => $user->profile_image_url,
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                        ];
                    })->values(),
                    'status' => (int) $user->status,
                    'deleted_at' => $user->deleted_at ? $user->deleted_at->toDateTimeString() : null,
                    'created_at_human' => optional($user->created_at)->format('d M Y, h:i A'),

                    ...$this->superAdminAuditMeta($user, $authUser),
                    'show_url' => route('admin.users.show', $user->id),
                    'edit_url' => route('admin.users.edit', $user->id),
                    'delete_url' => route('admin.users.delete', $user->id),
                    'restore_url' => route('admin.users.revoke', $user->id),

                    'permissions' => [
                        'can_view' => $authUser->can('User_ViewAll') || $authUser->can('User_ViewMine') || $authUser->can('User_View'),
                        'can_edit' => $authUser->can('User_Edit'),
                        'can_delete' => $authUser->can('User_Delete'),
                        'can_restore' => $authUser->can('User_Revoke'),
                    ],
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'My users fetched successfully.',
                'data' => [
                    'items' => $list,
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                        'from' => $users->firstItem(),
                        'to' => $users->lastItem(),
                        'has_more_pages' => $users->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $is_deleted,
                    ],
                ],
            ]);
        }

        return view('admin.users.index', compact('users'));
    }
    public function createUser()
    {
        $roles = SystemVisibility::selectableRoles()
            ->orderBy('name')
            ->get();

        return view('admin.users.create', compact('roles'));
    }

    public function postUser(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        $role = SystemVisibility::selectableRoles()
            ->find($validated['role_id']);

        if (!$role) {
            return back()->withInput()->with('error', 'Selected role not found.');
        }

        $profileImagePath = $this->storeProfileImage($request);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'cnic' => $validated['cnic'] ?? null,
            'passport' => $validated['passport'] ?? null,
            'status' => $validated['status'],
            'emp_id' => $validated['emp_id'] ?? null,
            'father_name' => $validated['father_name'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'wife_name' => $validated['wife_name'] ?? null,
            'profile_image' => $profileImagePath,
            'created_by' => Auth::id(),
        ]);

        $user->syncRoles([$role->name]);

        return redirect()
            ->route('admin.users')
            ->with('success', 'User added successfully.');
    }

    public function showUser($id)
    {
        $user = User::withTrashed()
            ->with(['roles', 'createdByUser', 'updatedByUser', 'deletedByUser'])
            ->find($id);

        if (!$user || SystemVisibility::isSuperAdminUser($user)) {
            return redirect()->route('admin.users')->with('error', 'User not found.');
        }

        return view('admin.users.show', compact('user'));
    }

    public function editUser($id)
    {
        $user = User::with('roles')->find($id);

        if (!$user || SystemVisibility::isSuperAdminUser($user)) {
            return back()->with('error', 'User not found.');
        }

        $roles = SystemVisibility::selectableRoles()
            ->orderBy('name')
            ->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::with('roles')->find($id);

        if (!$user || SystemVisibility::isSuperAdminUser($user)) {
            return back()->with('error', 'User not found.');
        }

        $validated = $request->validate($this->validationRules($id, true));

        if (!empty($validated['role_id'])) {
            $role = SystemVisibility::selectableRoles()
                ->find($validated['role_id']);

            if (!$role) {
                return back()->withInput()->with('error', 'Selected role not found.');
            }
        }

        if ($request->boolean('remove_profile_image')) {
            $this->deleteProfileImage($user->profile_image);
            $user->profile_image = null;
        }

        if ($request->hasFile('profile_image')) {
            $this->deleteProfileImage($user->profile_image);
            $user->profile_image = $this->storeProfileImage($request);
        }

        $user->name = $validated['name'] ?? $user->name;
        $user->email = $validated['email'] ?? $user->email;
        $user->phone = $validated['phone'] ?? null;
        $user->address = $validated['address'] ?? null;
        $user->dob = $validated['dob'] ?? null;
        $user->cnic = $validated['cnic'] ?? null;
        $user->passport = $validated['passport'] ?? null;
        $user->emp_id = $validated['emp_id'] ?? null;
        $user->father_name = $validated['father_name'] ?? null;
        $user->mother_name = $validated['mother_name'] ?? null;
        $user->wife_name = $validated['wife_name'] ?? null;

        if (array_key_exists('status', $validated)) {
            $user->status = $validated['status'];
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);

            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }
        }

        $user->updated_by = Auth::id();
        $user->save();

        if (!empty($validated['role_id']) && isset($role)) {
            $user->syncRoles([$role->name]);
        }

        return redirect()
            ->route('admin.users')
            ->with('success', 'User updated successfully.');
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::find($id);

        if (!$user || SystemVisibility::isSuperAdminUser($user->loadMissing('roles'))) {
            return back()->with('error', 'User not found.');
        }

        $user->password = Hash::make($request->password);
        $user->updated_by = Auth::id();
        $user->save();

        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return back()->with('success', 'Password changed successfully.');
    }

    public function deleteUser($id)
    {
        $user = User::with('roles')->find($id);

        if (!$user || SystemVisibility::isSuperAdminUser($user)) {
            return back()->with('error', 'User not found.');
        }

        $user->deleted_by = Auth::id();
        $user->save();
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    public function revokeUser($id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user || SystemVisibility::isSuperAdminUser($user->loadMissing('roles'))) {
            return back()->with('error', 'User not found.');
        }

        if (is_null($user->deleted_at)) {
            return back()->with('error', 'User is not deleted.');
        }

        $user->restore();
        $user->deleted_by = null;
        $user->save();

        return redirect()->route('admin.users')->with('success', 'User restored successfully.');
    }

    private function validationRules(?int $userId = null, bool $isUpdate = false): array
    {
        $passwordRules = $isUpdate
            ? ['nullable', 'string', 'min:6', 'confirmed']
            : ['required', 'string', 'min:6', 'confirmed'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => $passwordRules,
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(function ($query) {
                    $query->where('guard_name', 'web')
                        ->where('id', '!=', SystemVisibility::superAdminRoleId())
                        ->whereNull('deleted_at');
                }),
            ],
            'status' => ['required', 'numeric', 'in:0,1'],

            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'dob' => ['nullable', 'date'],
            'cnic' => ['nullable', 'string', 'max:50'],
            'passport' => ['nullable', 'string', 'max:50'],
            'emp_id' => ['nullable', 'string', 'max:100'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'wife_name' => ['nullable', 'string', 'max:255'],

            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_image' => ['nullable', 'in:0,1'],
        ];
    }

    private function storeProfileImage(Request $request): ?string
    {
        if (!$request->hasFile('profile_image')) {
            return null;
        }

        $file = $request->file('profile_image');
        $folder = 'blogs/' . now()->format('FY');
        $fileName = Str::random(22) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $fileName, 'public');
    }

    private function deleteProfileImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function exportUsers($usersQuery, bool $isDeleted = false)
    {
        $users = $usersQuery->get();

        $callback = function () use ($users, $isDeleted) {
            $file = fopen('php://output', 'w');

            $headers = [
                'ID',
                'Name',
                'Email',
                'Phone',
                'EMP ID',
                'CNIC',
                'Passport',
                'Role',
                'Status',
                'Created By',
                'Updated By',
                'Created At',
                'Updated At',
            ];

            if ($isDeleted) {
                $headers[] = 'Deleted By';
                $headers[] = 'Deleted At';
            }

            fputcsv($file, $headers);

            foreach ($users as $user) {
                $row = [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->emp_id,
                    $user->cnic,
                    $user->passport,
                    optional($user->roles->first())->name,
                    (int) $user->status,
                    optional($user->createdByUser)->name,
                    optional($user->updatedByUser)->name,
                    optional($user->created_at)->format('Y-m-d H:i:s'),
                    optional($user->updated_at)->format('Y-m-d H:i:s'),
                ];

                if ($isDeleted) {
                    $row[] = optional($user->deletedByUser)->name;
                    $row[] = optional($user->deleted_at)->format('Y-m-d H:i:s');
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=users.csv',
        ]);
    }
}

