<aside id="sidebar"
    class="fixed left-0 top-0 z-40 h-screen w-[212px] min-w-[212px] max-w-[calc(100vw-16px)] shrink-0 overflow-x-hidden overflow-y-auto border-r border-[#1f2b42] bg-[#071427] text-white shadow-[14px_0_42px_rgba(2,6,23,0.35)] transition-transform duration-300 ease-out lg:static lg:z-20 lg:h-screen lg:translate-x-0 lg:shadow-none">

    <div class="relative flex h-full flex-col">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-16 left-[-40px] h-40 w-40 rounded-full bg-[#d9b55a]/10 blur-3xl"></div>
            <div class="absolute bottom-10 right-[-30px] h-36 w-36 rounded-full bg-[#c79a2b]/10 blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.03]"
                style="background-image: linear-gradient(rgba(255,255,255,0.7) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.7) 1px, transparent 1px); background-size: 22px 22px;">
            </div>
        </div>

        <div class="relative flex min-h-[76px] items-center border-b border-white/10 px-2">
            <div class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-[#f8dc7b] via-[#d6ab3d] to-[#b9871a] text-[#071427] shadow-[0_10px_24px_rgba(214,171,61,0.32)] ring-1 ring-white/20">
                    <span class="text-[13px] font-black tracking-wide">FD</span>
                </div>

                <div class="min-w-0 sidebar-brand-text">
                    <p class="mb-0.5 text-[9px] uppercase tracking-[0.28em] text-[#c5a95a]">Admin Panel</p>
                    <h2 class="truncate text-[1.02rem] font-bold leading-none text-white">FalconDrive</h2>
                </div>
            </div>
        </div>

        <div class="relative px-1.5 pt-1.5">
            <div class="overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-white/10 to-white/[0.03] p-2 shadow-[inset_0_1px_0_rgba(255,255,255,0.04)] backdrop-blur-sm">
                <div class="flex items-center gap-2">
                    <div class="relative flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-[#d9b55a]/25 bg-[#0c1d35] ring-1 ring-white/5">
                        @if(auth()->check() && auth()->user()->profile_image)
                            <img src="{{ auth()->user()->profile_image_url ?? auth()->user()->profile_image }}" alt="Profile"
                                class="h-full w-full rounded-xl object-cover">
                        @else
                            <i class="fas fa-user-shield text-[11px] text-[#d9b55a]"></i>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1 sidebar-user-details">
                        <p class="truncate text-[12px] font-semibold text-white">
                            {{ auth()->user()->name ?? 'Admin User' }}
                        </p>
                        <p class="truncate text-[10px] text-slate-300">
                            {{ auth()->user()->email ?? 'admin@example.com' }}
                        </p>
                    </div>
                </div>

                @if(auth()->check() && auth()->user()->roles->count())
                    <div class="mt-2 flex flex-wrap gap-1 sidebar-role-badges">
                        @foreach(auth()->user()->roles as $role)
                            <span class="inline-flex rounded-full border border-[#d9b55a]/25 bg-[#d9b55a]/12 px-1.5 py-0.5 text-[9px] font-medium text-[#f3d67b]">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <nav class="flex-1 space-y-0.5 px-1.5 py-1 text-[11px]">
            @can('Dashboard_View')
                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-link flex items-center gap-2 rounded-lg px-2 py-1.5 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#e0bc5a]/22 text-[#fff7dc] ring-1 ring-[#e0bc5a]/25' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-[#0c1d35] {{ request()->routeIs('admin.dashboard') ? 'text-[#f8dd7c]' : 'text-slate-200' }}">
                        <i class="fas fa-gauge-high text-[11px]"></i>
                    </span>
                    <span class="truncate sidebar-text">Dashboard</span>
                </a>
            @endcan

            @can('User_Menu')
                <a href="{{ route('admin.users') }}"
                    class="sidebar-link flex items-center gap-2 rounded-lg px-2 py-1.5 transition-colors {{ request()->routeIs('admin.users') || request()->routeIs('admin.users.*') ? 'bg-[#e0bc5a]/22 text-[#fff7dc] ring-1 ring-[#e0bc5a]/25' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-[#0c1d35] {{ request()->routeIs('admin.users') || request()->routeIs('admin.users.*') ? 'text-[#f8dd7c]' : 'text-slate-200' }}">
                        <i class="fas fa-users text-[11px]"></i>
                    </span>
                    <span class="truncate sidebar-text">Users</span>
                </a>
            @endcan

            @can('Role_Menu')
                <a href="{{ route('admin.roles') }}"
                    class="sidebar-link flex items-center gap-2 rounded-lg px-2 py-1.5 transition-colors {{ request()->routeIs('admin.roles') || request()->routeIs('admin.roles.*') ? 'bg-[#e0bc5a]/22 text-[#fff7dc] ring-1 ring-[#e0bc5a]/25' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-[#0c1d35] {{ request()->routeIs('admin.roles') || request()->routeIs('admin.roles.*') ? 'text-[#f8dd7c]' : 'text-slate-200' }}">
                        <i class="fas fa-user-tag text-[11px]"></i>
                    </span>
                    <span class="truncate sidebar-text">Roles</span>
                </a>
            @endcan

            @can('Permissions_Menu')
                <a href="{{ route('admin.permissions') }}"
                    class="sidebar-link flex items-center gap-2 rounded-lg px-2 py-1.5 transition-colors {{ request()->routeIs('admin.permissions') || request()->routeIs('admin.permissions.*') ? 'bg-[#e0bc5a]/22 text-[#fff7dc] ring-1 ring-[#e0bc5a]/25' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-[#0c1d35] {{ request()->routeIs('admin.permissions') || request()->routeIs('admin.permissions.*') ? 'text-[#f8dd7c]' : 'text-slate-200' }}">
                        <i class="fas fa-key text-[11px]"></i>
                    </span>
                    <span class="truncate sidebar-text">Permissions</span>
                </a>
            @endcan

            @if(\App\Support\SystemVisibility::isSuperAdminUser(auth()->user()) && auth()->user()->can('ActivityLogs_Menu'))
                <a href="{{ route('admin.activity-logs') }}"
                    class="sidebar-link flex items-center gap-2 rounded-lg px-2 py-1.5 transition-colors {{ request()->routeIs('admin.activity-logs') || request()->routeIs('admin.activity-logs.*') ? 'bg-[#e0bc5a]/22 text-[#fff7dc] ring-1 ring-[#e0bc5a]/25' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-[#0c1d35] {{ request()->routeIs('admin.activity-logs') || request()->routeIs('admin.activity-logs.*') ? 'text-[#f8dd7c]' : 'text-slate-200' }}">
                        <i class="fas fa-clock-rotate-left text-[11px]"></i>
                    </span>
                    <span class="truncate sidebar-text">Activity Logs</span>
                </a>
            @endif
        </nav>

        <div class="relative border-t border-white/10 px-1.5 pb-2 pt-1.5">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="sidebar-link flex w-full items-center gap-2 rounded-lg border border-red-400/15 bg-red-500/[0.04] px-2 py-1.5 text-red-300 transition-colors duration-150 hover:border-red-400/30 hover:bg-red-500 hover:text-white">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-[#311621] text-[11px]">
                        <i class="fas fa-right-from-bracket"></i>
                    </span>
                    <span class="truncate font-medium sidebar-text">Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>