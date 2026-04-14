<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Traits\ImageUrlTrait;

class AuthController extends Controller
{
    use ImageUrlTrait;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function login(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Attempt to log the user in (active users only)
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'status' => 1])) {
            // Authentication passed, get the authenticated user
            $user = Auth::user();

            $role = $user->roles->first(); // Get first assigned role
            $roleId = $roleName = null;
            if ($role) {
                $roleId = $role->id;        // Role ID
                $roleName = $role->name;    // Role name
            }
            
            // Get user roles
            $role = $user->getRoleNames()->first();
            $profile_image = $user->profile_image ? $this->getImageUrl($user->profile_image) : null;

            // Generate a token (you can use Laravel Sanctum or JWT here)
            $token = $user->createToken('authToken')->plainTextToken; // For Sanctum
            
            UserActivityLog::create([
                'user_id' => $user->id,
                'model_type' => User::class,
                'action' => 'login'
            ]);

            return response()->json([
                'status' => true,
                "message" => "You have logged in successfully.",
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => (int) $user->status,
                    'role_id' => $roleId,
                    'role_name' => $roleName,
                    'api_token' => $token,
                    'profile_image' => $profile_image
                ],
            ], Response::HTTP_OK);
        } else {
            $inactiveUser = User::where('email', $request->email)->where('status', 0)->first();

            if ($inactiveUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account is inactive. Please contact the admin to have your account activated first.'
                ], Response::HTTP_UNAUTHORIZED);
            }

            return response()->json([
                'status' => false,
                'message' => 'Credentials do not match!'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function register(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
      
        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 0
        ]);

        // Generate an API token (if using Sanctum)
        $token = $user->createToken('authToken')->plainTextToken;
        
        // Return a response
        return response()->json([
            'status' => true,
            'message' => 'User registered successfully!',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => (int) $user->status,
                'role_id' => null,
                'role_name' => null,
                'api_token' => $token,
                'profile_image' => null
            ],
        ], Response::HTTP_CREATED); // HTTP 201 Created
    }

    public function logout(Request $request)
    {
        
        $user = auth()->user();

        UserActivityLog::create([
            'user_id' => $user->id,
            'model_type' => User::class,
            'action' => 'logout'
        ]);
    
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Successfully logged out'
        ], Response::HTTP_OK);
    }

}
