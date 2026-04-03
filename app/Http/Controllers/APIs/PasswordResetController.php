<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\SendOtpNotification;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
     // Step 1: Forget Password (Send OTP)
    public function forgetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found!'
            ], 404);
        } elseif ($user->id == 1) {
            return response()->json([
                'status' => false,
                'message' => 'Password update not allowed for Super Admin.'
            ], 403);
        }

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(5);
        $user->save();

        $user->notify(new SendOtpNotification($otp));

        return response()->json([
                    'status' => true,
                    'message' => 'OTP sent to email.'
                ], Response::HTTP_OK);
    }

    // Step 2: Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required'
        ]);

        $user = User::where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->first();

        if (!$user) return response()->json(['status' => false,'message' => 'Invalid OTP.'], 422);

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['status' => false,'message' => 'OTP expired.'], 422);
        }

        return response()->json([
                    'status' => true,
                    'message' => 'OTP verified.'
                ], Response::HTTP_OK);
    }

    // Step 3: Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'password'              => 'required|string|confirmed',
            'password_confirmation' => 'required'
        ]);

        $user = User::where('email', $request->email)
                    ->first();

        if (!$user) return response()->json(['status' => false,'message' => 'Invalid OTP.'], 422);

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['status' => false, 'message' => 'OTP expired.'], 422);
        }

        $user->password = Hash::make($request->password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
                    'status' => true,
                    'message' => 'Password reset successfully.'
                ], Response::HTTP_OK);
    }
}
