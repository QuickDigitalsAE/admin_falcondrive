<aside id="sidebar"
    class="fixed lg:sticky top-0 left-0 z-40 h-screen w-[250px] min-w-[250px] bg-[#071427] text-white border-r border-[#1f2b42] transform -translate-x-full lg:translate-x-0 transition-all duration-300 overflow-hidden shrink-0">

    <div class="relative flex h-full flex-col">
        <!-- Background accents -->
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-16 left-[-40px] h-40 w-40 rounded-full bg-[#d9b55a]/10 blur-3xl"></div>
            <div class="absolute bottom-10 right-[-30px] h-36 w-36 rounded-full bg-[#c79a2b]/10 blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.03]"
                style="background-image: linear-gradient(rgba(255,255,255,0.7) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.7) 1px, transparent 1px); background-size: 22px 22px;">
            </div>
        </div>

        <!-- Brand -->
        <div class="relative border-b border-white/10 px-3 py-3">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-[#f8dc7b] via-[#d6ab3d] to-[#b9871a] text-[#071427] shadow-[0_10px_24px_rgba(214,171,61,0.32)] ring-1 ring-white/20">
                    <span class="text-[14px] font-black tracking-wide">FD</span>
                </div>

                <div class="sidebar-text min-w-0">
                    <p class="mb-0.5 text-[9px] uppercase tracking-[0.28em] text-[#c5a95a]">Admin Panel</p>
                    <h2 class="truncate text-[1.3rem] font-bold leading-none text-white">FalconDrive</h2>
                </div>
            </div>
        </div>

        <!-- User Card -->
        <div class="relative px-3 pt-3">
            <div
                class="sidebar-user-card overflow-hidden rounded-[20px] border border-white/10 bg-gradient-to-br from-white/10 to-white/[0.03] p-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.04)] backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <div
                        class="relative flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-[#d9b55a]/25 bg-[#0c1d35] ring-1 ring-white/5">
                        @if(auth()->check() && auth()->user()->profile_image)
                            <img src="{{ auth()->user()->profile_image_url ?? auth()->user()->profile_image }}" alt="Profile"
                                class="h-full w-full rounded-2xl object-cover">
                        @else
                            <i class="fas fa-user-shield text-[#d9b55a] text-[12px]"></i>
                        @endif
                    </div>

                    <div class="sidebar-text min-w-0 flex-1">
                        <p class="truncate text-[13px] font-semibold text-white">
                            {{ auth()->user()->name ?? 'Admin User' }}
                        </p>
                        <p class="truncate text-[11px] text-slate-300">
                            {{ auth()->user()->email ?? 'admin@example.com' }}
                        </p>
                    </div>
                </div>

                @if(auth()->check() && auth()->user()->roles->count())
                    <div class="sidebar-text mt-2.5 flex flex-wrap gap-1.5">
                        @foreach(auth()->user()->roles as $role)
                            <span
                                class="inline-flex rounded-full border border-[#d9b55a]/25 bg-[#d9b55a]/12 px-2 py-1 text-[10px] font-medium text-[#f3d67b]">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Nav -->
        <nav class="relative flex-1 space-y-1.5 px-3 py-4 text-[13px]">
            @can('Dashboard_View')
                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-link group flex items-center gap-3 rounded-[16px] border px-3 py-2.5 transition-all duration-200
                    {{ request()->routeIs('admin.dashboard')
                        ? 'border-[#d9b55a]/25 bg-gradient-to-r from-[#d9b55a]/18 to-[#d9b55a]/8 text-white shadow-[0_10px_24px_rgba(0,0,0,0.16)]'
                        : 'border-transparent text-slate-300 hover:border-white/10 hover:bg-white/[0.05] hover:text-white' }}">

                    <span
                        class="sidebar-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border transition-all duration-200
                        {{ request()->routeIs('admin.dashboard')
                            ? 'border-[#d9b55a]/30 bg-[#d9b55a]/18 text-[#f5d86c]'
                            : 'border-white/5 bg-[#0c1d35] text-slate-400 group-hover:text-[#f5d86c]' }}">
                        <i class="fas fa-gauge-high text-[12px]"></i>
                    </span>

                    <span class="sidebar-text flex-1 text-[12.5px] font-semibold">Dashboard</span>
                </a>
            @endcan

            @can('User_Menu')
                <a href="{{ route('admin.users') }}"
                    class="sidebar-link group flex items-center gap-3 rounded-[16px] border px-3 py-2.5 transition-all duration-200
                    {{ request()->routeIs('admin.users') || request()->routeIs('admin.users.*')
                        ? 'border-[#d9b55a]/25 bg-gradient-to-r from-[#d9b55a]/18 to-[#d9b55a]/8 text-white shadow-[0_10px_24px_rgba(0,0,0,0.16)]'
                        : 'border-transparent text-slate-300 hover:border-white/10 hover:bg-white/[0.05] hover:text-white' }}">

                    <span
                        class="sidebar-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border transition-all duration-200
                        {{ request()->routeIs('admin.users') || request()->routeIs('admin.users.*')
                            ? 'border-[#d9b55a]/30 bg-[#d9b55a]/18 text-[#f5d86c]'
                            : 'border-white/5 bg-[#0c1d35] text-slate-400 group-hover:text-[#f5d86c]' }}">
                        <i class="fas fa-users text-[12px]"></i>
                    </span>

                    <span class="sidebar-text flex-1 text-[12.5px] font-semibold">Users</span>
                </a>
            @endcan
        </nav>

        <!-- Logout -->
        <div class="relative border-t border-white/10 px-3 pb-4 pt-3">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="sidebar-link flex w-full items-center gap-3 rounded-[16px] border border-red-400/15 bg-red-500/[0.04] px-3 py-2.5 text-red-300 transition-all duration-200 hover:border-red-400/30 hover:bg-red-500 hover:text-white">
                    <span
                        class="sidebar-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#311621] text-[12px]">
                        <i class="fas fa-right-from-bracket"></i>
                    </span>
                    <span class="sidebar-text text-[12.5px] font-semibold">Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>