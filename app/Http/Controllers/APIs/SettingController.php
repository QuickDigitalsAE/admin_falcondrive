<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Traits\ImageUrlTrait;

class SettingController extends Controller
{
    use ImageUrlTrait;

    public function getMyProfile()
    {
        // Get the currently authenticated user
        $user = User::with('roles')->find(Auth::id());

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found!',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        $profile_image = $user->profile_image ? $this->getImageUrl($user->profile_image) : null;

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
            $roleId = $role->id;
            $roleName = $role->name;
        }

        return response()->json([
            'status' => true,
            'message' => 'My profile details fetched successfully!',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'date_of_birth' => $user->dob,
                'cnic' => $user->cnic,
                'passport' => $user->passport,
                'profile_image' => $profile_image,
                'role_id' => $roleId,
                'role_name' => $roleName,
                'status' => (int) $user->status,
                'permissions' => $permissions_grouped
            ]
        ], Response::HTTP_OK);
    }

    public function updateProfile(Request $request)
    {
        $user_id = Auth::id();

        // Validate the request
        $validator = [
            'name' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string',
            'address' => 'sometimes|required|string',
            'date_of_birth' => 'sometimes|required|string',
            'cnic' => 'sometimes|required|string',
            'passport' => 'sometimes|required|string',
            'profile_image' => 'nullable|string',
        ];
        
        $request->validate($validator);

        // Find the user with roles
        $user = User::with('roles')->find($user_id);
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found!',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        // Update user details
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        if ($request->has('address')) {
            $user->address = $request->address;
        }

        if ($request->has('date_of_birth')) {
            $user->dob = $request->date_of_birth;
        }

        if ($request->has('cnic')) {
            $user->cnic = $request->cnic;
        }

        if ($request->has('passport')) {
            $user->passport = $request->passport;
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
            'message' => 'Profile updated successfully!',
            'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'date_of_birth' => $user->dob,
                    'cnic' => $user->cnic,
                    'passport' => $user->passport,
                    'status' => (int) $user->status,
                    'role_id' => $roleId,
                    'role_name' => $roleName,
                    'profile_image' => $profile_image
                ]
        ], Response::HTTP_OK);
    }
    
    public function myChangePassword(Request $request)
    {
        // Validate input
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Get the currently authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found!',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        // Update password and updated_by field
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
        ], Response::HTTP_OK);
    }
}
