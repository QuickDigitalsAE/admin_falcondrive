<?php

namespace App\Http\Controllers\APIs;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends BaseApiController
{
    use ApiResponseTrait;

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
                'device_name' => ['nullable', 'string', 'max:100'],
            ]);

            $userClass = config('auth.providers.users.model');
            $user = $userClass::where('email', $validated['email'])->first();

            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse('Invalid login credentials', new \stdClass(), 401);
            }

            $token = $user->createToken($validated['device_name'] ?? 'falcondrive-api')->plainTextToken;

            return $this->successResponse('Login successful', [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', ['errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            return $this->errorResponse($e->getMessage(), ['exception' => class_basename($e)], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();
        return $this->successResponse('Logout successful', new \stdClass());
    }

    public function me(Request $request)
    {
        return $this->successResponse('Authenticated user fetched successfully', [
            'user' => $request->user(),
        ]);
    }
}
