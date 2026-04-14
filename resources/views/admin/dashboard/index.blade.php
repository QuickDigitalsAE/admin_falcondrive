@extends('admin.layouts.app')

@section('page_title', 'Dashboard')
@section('page_subtitle', 'Overview of your system performance')

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-4">
            <div class="rounded-[26px] border border-[#e7dcc1] bg-white px-5 py-5 shadow-[0_10px_30px_rgba(15,23,42,0.06)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Users</p>
                        <h3 class="mt-2 text-[32px] font-bold leading-none text-slate-900">{{ number_format($totalUsers ?? 0) }}</h3>
                        <p class="mt-3 text-xs text-slate-500">All non-superadmin users currently in the panel.</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff5d8] text-[#b88016]">
                        <i class="fas fa-users text-[16px]"></i>
                    </span>
                </div>
            </div>

            <div class="rounded-[26px] border border-[#e7dcc1] bg-white px-5 py-5 shadow-[0_10px_30px_rgba(15,23,42,0.06)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Active Users</p>
                        <h3 class="mt-2 text-[32px] font-bold leading-none text-slate-900">{{ number_format($activeUsers ?? 0) }}</h3>
                        <p class="mt-3 text-xs text-slate-500">Users with active panel access excluding role ID 1.</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff5d8] text-[#b88016]">
                        <i class="fas fa-user-check text-[16px]"></i>
                    </span>
                </div>
            </div>

            <div class="rounded-[26px] border border-[#e7dcc1] bg-white px-5 py-5 shadow-[0_10px_30px_rgba(15,23,42,0.06)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Roles</p>
                        <h3 class="mt-2 text-[32px] font-bold leading-none text-slate-900">{{ number_format($totalRoles ?? 0) }}</h3>
                        <p class="mt-3 text-xs text-slate-500">All available business roles except the root role.</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff5d8] text-[#b88016]">
                        <i class="fas fa-user-tag text-[16px]"></i>
                    </span>
                </div>
            </div>

            <div class="rounded-[26px] border border-[#e7dcc1] bg-white px-5 py-5 shadow-[0_10px_30px_rgba(15,23,42,0.06)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Permissions</p>
                        <h3 class="mt-2 text-[32px] font-bold leading-none text-slate-900">{{ number_format($totalPermissions ?? 0) }}</h3>
                        <p class="mt-3 text-xs text-slate-500">Current permission actions configured for the admin panel.</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff5d8] text-[#b88016]">
                        <i class="fas fa-key text-[16px]"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-[1.45fr_0.95fr]">
            <div class="space-y-6">
                <div class="rounded-[28px] border border-[#e7dcc1] bg-white shadow-[0_15px_40px_rgba(15,23,42,0.08)]">
                    <div class="flex items-center justify-between gap-3 border-b border-[#f0e6ca] px-5 py-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Quick Access</p>
                            <h2 class="mt-1 text-xl font-bold text-slate-900">Modules</h2>
                        </div>
                        <span class="rounded-full bg-[#fff5d8] px-3 py-1 text-xs font-semibold text-[#8a6a1c]">{{ ($modules ?? collect())->count() }} active</span>
                    </div>

                    <div class="grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-3">
                        @forelse(($modules ?? collect()) as $module)
                            <a href="{{ $module['url'] }}"
                                class="group rounded-[22px] border border-[#eadfbe] bg-[#fffdf8] px-4 py-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#d9bf77] hover:bg-white">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#fff5d8] text-[#b88016] transition group-hover:bg-[#f9e4a4]">
                                        <i class="fas {{ $module['icon'] }} text-[14px]"></i>
                                    </span>
                                    @if(!is_null($module['count']))
                                        <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-[#eadfbe]">
                                            {{ number_format($module['count']) }}
                                        </span>
                                    @endif
                                </div>
                                <h3 class="mt-4 text-sm font-bold text-slate-900">{{ $module['title'] }}</h3>
                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ $module['description'] }}</p>
                            </a>
                        @empty
                            <div class="col-span-full rounded-[22px] border border-dashed border-[#eadfbe] bg-[#fffdf8] px-5 py-10 text-center text-sm text-slate-500">
                                No dashboard modules available for this user.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[28px] border border-[#e7dcc1] bg-white shadow-[0_15px_40px_rgba(15,23,42,0.08)]">
                    <div class="flex items-center justify-between gap-3 border-b border-[#f0e6ca] px-5 py-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Directory</p>
                            <h2 class="mt-1 text-xl font-bold text-slate-900">Latest Users</h2>
                        </div>
                        <span class="rounded-full bg-[#fff5d8] px-3 py-1 text-xs font-semibold text-[#8a6a1c]">Role 1 excluded</span>
                    </div>

                    <div class="space-y-3 p-4">
                        @forelse(($latestUsers ?? []) as $user)
                            <div class="flex items-center justify-between gap-4 rounded-[22px] border border-[#eadfbe] bg-[#fffdf8] px-4 py-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <img src="{{ $user->profile_image_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'User') . '&background=F8E8B2&color=5E450A&size=200' }}"
                                        alt="{{ $user->name }}" class="h-12 w-12 rounded-2xl object-cover ring-2 ring-[#f4e3ab]">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                                        <p class="mt-1 text-[11px] font-medium text-[#9b7a28]">
                                            {{ optional($user->roles->first())->name ?: 'No role assigned' }}
                                        </p>
                                    </div>
                                </div>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ (int) $user->status === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ (int) $user->status === 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        @empty
                            <div class="rounded-[22px] border border-dashed border-[#eadfbe] bg-[#fffdf8] px-5 py-10 text-center text-sm text-slate-500">
                                No users found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[28px] border border-[#e7dcc1] bg-white shadow-[0_15px_40px_rgba(15,23,42,0.08)]">
                    <div class="border-b border-[#f0e6ca] px-5 py-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Brand Theme</p>
                        <h2 class="mt-1 text-xl font-bold text-slate-900">FalconDrive Admin UI</h2>
                    </div>

                    <div class="space-y-3 p-4">
                        <div class="rounded-[20px] border border-[#eadfbe] bg-[#fffdf8] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Primary</p>
                            <div class="mt-3 flex items-center gap-3">
                                <span class="h-8 w-8 rounded-full bg-[#f4dc7c] ring-4 ring-[#fff1c2]"></span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">#f4dc7c</p>
                                    <p class="text-xs text-slate-500">Highlight / CTA / badge tone</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[20px] border border-[#eadfbe] bg-[#fffdf8] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Secondary</p>
                            <div class="mt-3 flex items-center gap-3">
                                <span class="h-8 w-8 rounded-full bg-[#b77b1e] ring-4 ring-[#f8e1b4]"></span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">#b77b1e</p>
                                    <p class="text-xs text-slate-500">Accent / hover / active state</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[20px] border border-dashed border-[#d8c79d] bg-[#fffaf0] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Included Auth Pages</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Login, forgot password, reset password, profile, and account settings are aligned with the same admin UI theme.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[28px] border border-[#e7dcc1] bg-white shadow-[0_15px_40px_rgba(15,23,42,0.08)]">
                    <div class="border-b border-[#f0e6ca] px-5 py-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Latest Activity</p>
                        <h2 class="mt-1 text-xl font-bold text-slate-900">Recent Admin Logs</h2>
                    </div>

                    <div class="space-y-2 p-4">
                        @forelse(($recentActivity ?? []) as $activity)
                            <div class="flex items-center justify-between gap-3 rounded-[18px] border border-[#f0e6ca] bg-[#fffdf8] px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $activity['title'] }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $activity['meta'] }}</p>
                                </div>
                                <span class="shrink-0 text-[11px] font-medium text-slate-400">{{ $activity['time'] }}</span>
                            </div>
                        @empty
                            <div class="rounded-[22px] border border-dashed border-[#eadfbe] bg-[#fffdf8] px-5 py-10 text-center text-sm text-slate-500">
                                No recent activity available.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
