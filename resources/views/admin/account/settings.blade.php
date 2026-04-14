@extends('admin.layouts.app')

@section('title', 'Settings')
@section('page_title', 'Settings')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Settings</span>
    </nav>
@endsection

@section('content')
    @php
        $primaryRole = optional($user->roles->first())->name;
    @endphp

    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl space-y-6">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div
                    class="grid gap-6 border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-5 py-6 sm:px-6 lg:grid-cols-[1.5fr_0.9fr]">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Security</p>
                        <h1 class="mt-1 text-[28px] font-bold leading-tight text-slate-900">Account Settings</h1>
                        <p class="mt-2 max-w-2xl text-sm text-slate-500">
                            Manage password security and review the account information currently tied to your login.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-[22px] border border-[#eadfbe] bg-white px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Role</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $primaryRole ? ucwords(str_replace(['-', '_'], ' ', $primaryRole)) : 'No role assigned' }}
                            </p>
                        </div>
                        <div class="rounded-[22px] border border-[#eadfbe] bg-white px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Status</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ (int) $user->status === 1 ? 'Active' : 'Inactive' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 p-5 sm:p-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <div class="space-y-6">
                        <form action="{{ route('admin.account.settings.password') }}" method="POST"
                            class="overflow-hidden rounded-[24px] border border-[#eadfbe] bg-[#fffdf8]">
                            @csrf
                            @method('PUT')

                            <div class="border-b border-[#f0e6ca] bg-white px-5 py-5">
                                <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Password</p>
                                <h2 class="mt-1 text-xl font-bold text-slate-900">Change Password</h2>
                                <p class="mt-1 text-sm text-slate-500">Use your current password to confirm this action.</p>
                            </div>

                            <div class="space-y-5 p-5">
                                <div class="space-y-2">
                                    <label for="current_password" class="text-sm font-semibold text-slate-700">Current Password</label>
                                    <div class="relative">
                                        <input id="current_password" type="password" name="current_password"
                                            class="w-full rounded-[18px] border {{ $errors->has('current_password') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-white px-4 py-3 pr-12 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                        <button type="button"
                                            class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#a27d20]"
                                            data-target="current_password">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label for="password" class="text-sm font-semibold text-slate-700">New Password</label>
                                    <div class="relative">
                                        <input id="password" type="password" name="password"
                                            class="w-full rounded-[18px] border {{ $errors->has('password') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-white px-4 py-3 pr-12 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                        <button type="button"
                                            class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#a27d20]"
                                            data-target="password">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirm New Password</label>
                                    <div class="relative">
                                        <input id="password_confirmation" type="password" name="password_confirmation"
                                            class="w-full rounded-[18px] border border-[#e5d7b1] bg-white px-4 py-3 pr-12 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                        <button type="button"
                                            class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#a27d20]"
                                            data-target="password_confirmation">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-[20px] bg-[#d6ab3d] px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-[#c59626]">
                                    <i class="fas fa-shield-halved mr-2 text-[13px]"></i>
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="space-y-6">
                        <div class="overflow-hidden rounded-[24px] border border-[#eadfbe] bg-white shadow-sm">
                            <div class="border-b border-[#f0e6ca] bg-[#fffaf0] px-5 py-5">
                                <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Login Identity</p>
                                <h2 class="mt-1 text-xl font-bold text-slate-900">Account Overview</h2>
                            </div>

                            <div class="space-y-4 p-5">
                                <div class="rounded-[20px] border border-[#eadfbe] bg-[#fffdf8] px-4 py-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Name</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                                </div>

                                <div class="rounded-[20px] border border-[#eadfbe] bg-[#fffdf8] px-4 py-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Email</p>
                                    <p class="mt-2 break-all text-sm font-semibold text-slate-900">{{ $user->email }}</p>
                                </div>

                                <div class="rounded-[20px] border border-[#eadfbe] bg-[#fffdf8] px-4 py-4">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Member Since</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ optional($user->created_at)->format('d M Y, h:i A') ?: 'N/A' }}</p>
                                </div>

                                <a href="{{ route('admin.account.profile') }}"
                                    class="inline-flex w-full items-center justify-center rounded-[20px] border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] transition hover:bg-[#fff8e8]">
                                    <i class="far fa-user mr-2 text-[13px]"></i>
                                    Edit Profile Details
                                </a>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-[24px] border border-[#eadfbe] bg-[#071427] text-white shadow-sm">
                            <div class="px-5 py-5">
                                <p class="text-[11px] uppercase tracking-[0.24em] text-[#d4b563]">Security Notes</p>
                                <ul class="mt-4 space-y-3 text-sm text-slate-300">
                                    <li class="flex items-start gap-3">
                                        <i class="fas fa-check-circle mt-0.5 text-[#f3d67b]"></i>
                                        <span>Changing your password will update your web account credentials immediately.</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <i class="fas fa-check-circle mt-0.5 text-[#f3d67b]"></i>
                                        <span>API personal access tokens are cleared after a password change for security.</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <i class="fas fa-check-circle mt-0.5 text-[#f3d67b]"></i>
                                        <span>Use the profile page for photo, phone, address, and employee details.</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function () {
                    const target = document.getElementById(this.dataset.target);
                    const icon = this.querySelector('i');

                    if (!target) {
                        return;
                    }

                    if (target.type === 'password') {
                        target.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        target.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
        });
    </script>
@endpush
