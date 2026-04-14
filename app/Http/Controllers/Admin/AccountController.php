<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function editProfile()
    {
        $user = Auth::user()->loadMissing('roles');

        return view('admin.account.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
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
        ]);

        if ($request->boolean('remove_profile_image')) {
            $this->deleteProfileImage($user->profile_image);
            $user->profile_image = null;
        }

        if ($request->hasFile('profile_image')) {
            $this->deleteProfileImage($user->profile_image);
            $user->profile_image = $request->file('profile_image')->store('users/profile-images', 'public');
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'cnic' => $validated['cnic'] ?? null,
            'passport' => $validated['passport'] ?? null,
            'emp_id' => $validated['emp_id'] ?? null,
            'father_name' => $validated['father_name'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'wife_name' => $validated['wife_name'] ?? null,
            'updated_by' => $user->id,
        ]);

        $user->save();

        return redirect()
            ->route('admin.account.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function editSettings()
    {
        $user = Auth::user()->loadMissing('roles');

        return view('admin.account.settings', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($validated['password']);
        $user->updated_by = $user->id;
        $user->save();

        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return redirect()
            ->route('admin.account.settings')
            ->with('success', 'Password updated successfully.');
    }

    private function deleteProfileImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
