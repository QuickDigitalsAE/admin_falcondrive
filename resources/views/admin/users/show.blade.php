@extends('admin.layouts.app')

@section('title', 'User Details')
@section('page_title', 'User Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.users') }}" class="transition hover:text-[#9b7a28]">Users</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">User Details</span>
    </nav>
@endsection

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl space-y-6">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Users Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">User Profile</h1>
                            <p class="mt-1 text-sm text-slate-500">
                                Detailed view of selected user account information.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @can('User_Edit')
                                @if (is_null($user->deleted_at))
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]">
                                    <i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>
                                    Edit User
                                </a>
                                @endif
                            @endcan

                            <a href="{{ route('admin.users') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
                                <i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>
                                Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                        <!-- Profile Summary -->
                        <div class="xl:col-span-1">
                            <div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-5">
                                <div class="flex flex-col items-center text-center">
                                    <div class="h-28 w-28 overflow-hidden rounded-3xl border border-[#eadfbe] bg-white shadow-sm ring-4 ring-[#fbf2d6]">
                                        <img src="{{ $user->profile_image_url ? asset($user->profile_image_url) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'User') . '&background=F8E8B2&color=5E450A&size=200' }}"
                                            alt="{{ $user->name }}" class="h-full w-full object-cover">
                                    </div>

                                    <h2 class="mt-4 text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                                    <p class="mt-1 text-sm text-slate-500">{{ $user->email }}</p>

                                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                                        <span
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1
                                                {{ $user->status ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-red-50 text-red-700 ring-red-200' }}">
                                            <span class="mr-1.5 h-2 w-2 rounded-full {{ $user->status ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                            {{ $user->status ? 'Active' : 'Inactive' }}
                                        </span>

                                        @if($user->roles->count())
                                            @foreach($user->roles as $role)
                                                <span class="inline-flex items-center rounded-full bg-[#f8edd0] px-3 py-1 text-xs font-semibold text-[#8b6717] ring-1 ring-[#ecdca8]">
                                                    {{ ucwords(str_replace(['-', '_'], ' ', $role->name)) }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Details -->
                        <div class="xl:col-span-2">
                            <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <div class="mb-5">
                                    <h3 class="text-lg font-semibold text-slate-900">Account Information</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        View complete user account details and system information.
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Full Name</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $user->name ?: '—' }}</p>
                                    </div>

                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Email Address</p>
                                        <p class="mt-2 break-all text-sm font-semibold text-slate-900">
                                            {{ $user->email ?: '—' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Role</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-900">
                                            {{ optional($user->roles->first())->name ? ucwords(str_replace(['-', '_'], ' ', optional($user->roles->first())->name)) : '—' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Status</p>
                                        <p class="mt-2 text-sm font-semibold {{ $user->status ? 'text-emerald-700' : 'text-red-700' }}">
                                            {{ $user->status ? 'Active' : 'Inactive' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Created At</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-900">
                                            {{ $user->created_at ? $user->created_at->format('d M Y, h:i A') : '—' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Last Updated</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-900">
                                            {{ $user->updated_at ? $user->updated_at->format('d M Y, h:i A') : '—' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4 sm:col-span-2">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Profile Image Path</p>
                                        <p class="mt-2 break-all text-sm font-semibold text-slate-900">
                                            {{ $user->profile_image_url ?: 'No profile image uploaded' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 rounded-[24px] border border-dashed border-[#eadfbe] bg-[#fffdf8] px-5 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]">
                                        <i class="fa-solid fa-circle-info text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-semibold text-slate-800">Quick note</h3>
                                        <p class="mt-1 text-sm text-slate-500">
                                            Use the edit button to update role, profile image, password or status of this user.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
                        @can('User_Edit')
                            @if (is_null($user->deleted_at))
                            <a href="{{ route('admin.users.edit', $user->id) }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]">
                                <i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>
                                Edit User
                            </a>
                            @endif
                        @endcan

                        <a href="{{ route('admin.users') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
                            <i class="fa-solid fa-list mr-2 text-[13px]"></i>
                            All Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
