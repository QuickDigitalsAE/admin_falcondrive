<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PasswordController extends Controller
{
    
    // Send OTP
    public function forgot(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = Customer::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Email not found'
            ], 404);
        }

        $otp = rand(100000, 999999);

        // Delete old OTP
        PasswordReset::where('email', $request->email)->delete();

        // Store new OTP
        PasswordReset::create([
            'email' => $request->email,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(10)
        ]);

        try {

            Mail::raw("Your OTP for password reset is: {$otp}", function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Password Reset OTP');
            });

            return response()->json([
                'status' => true,
                'message' => 'OTP sent to your email'
            ], 200);

        } catch (\Exception $e) {

            // Delete OTP if mail sending failed
            PasswordReset::where('email', $request->email)->delete();

            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP email.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Reset Password
   public function resetPassword(Request $request)
    {
        // Validate email, OTP, password and confirm password
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed' // adds password_confirmation check
        ]);

        // Check OTP
        $record = PasswordReset::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$record) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP'
            ], 400);
        }

        if ($record->isExpired()) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired'
            ], 400);
        }

        // Update user password
        $user = Customer::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete OTP after reset
        $record->delete();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully'
        ]);
    }

    // Change Password
    public function changePassword(Request $request)
    {
        $user = $request->auth_user_id ? Customer::find($request->auth_user_id) : auth()->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed', // must have new_password_confirmation
        ]);

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Update to new password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}
