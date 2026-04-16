<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show Login Page
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    // Login Logic
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'status' => 1,
        ];
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            ActivityLogHelper::logAuth('login', Auth::user(), [
                'user_name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'ip_address' => ActivityLogHelper::resolveIpAddress($request),
                'user_agent' => (string) $request->userAgent(),
            ]);

            return redirect()->route('admin.dashboard')
                ->with('success', 'Login successful');
        }

        $user = User::withTrashed()->where('email', $request->input('email'))->first();

        ActivityLogHelper::logAuth('failed_login', $user, [
            'user_name' => $user?->name,
            'email' => (string) $request->input('email'),
            'ip_address' => ActivityLogHelper::resolveIpAddress($request),
            'user_agent' => (string) $request->userAgent(),
        ]);

        $message = ((int) ($user?->status ?? 1) === 0)
            ? 'Your account is inactive. Please contact admin.'
            : 'Invalid email or password.';

        return back()
            ->withErrors([
                'email' => $message,
            ])
            ->withInput($request->only('email', 'remember'));
    }

    // Logout
    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLogHelper::logAuth('logout', Auth::user(), [
                'user_name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'ip_address' => ActivityLogHelper::resolveIpAddress($request),
                'user_agent' => (string) $request->userAgent(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Logged out successfully');
    }
}
