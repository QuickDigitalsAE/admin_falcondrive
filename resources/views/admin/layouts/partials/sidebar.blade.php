<aside id="sidebar"
    class="fixed lg:sticky top-0 left-0 z-40 h-screen w-[232px] min-w-[232px] bg-[#0b1220] text-white border-r border-white/10 transform -translate-x-full lg:translate-x-0 transition-all duration-300 overflow-y-auto shrink-0">

    <div class="flex h-full flex-col">
        <!-- Brand -->
        <div class="px-3 pt-3 pb-2.5 border-b border-white/10">
            <div class="flex items-center gap-2">
                <div
                    class="w-9 h-9 rounded-xl bg-[#ffd21f] text-[#0b1220] flex items-center justify-center font-black text-[13px] shadow-md shrink-0">
                    FD
                </div>

                <div class="sidebar-text min-w-0">
                    <p class="text-[8px] uppercase tracking-[0.22em] text-slate-400 mb-0.5">Admin Panel</p>
                    <h2 class="text-[1.15rem] font-bold text-white leading-none truncate">FalconDrive</h2>
                </div>
            </div>
        </div>

        <!-- User Card -->
        <div class="px-2.5 pt-2.5">
            <div class="sidebar-user-card rounded-[14px] bg-white/5 border border-white/10 p-2">
                <div class="flex items-center gap-2">
                    <div
                        class="w-8 h-8 rounded-full bg-slate-800 border border-white/10 flex items-center justify-center shrink-0 relative overflow-hidden">
                        @if(auth()->check() && auth()->user()->profile_image)
                            <img src="{{ auth()->user()->profile_image }}" alt="Profile"
                                class="w-full h-full object-cover rounded-full">
                        @else
                            <i class="fas fa-user-shield text-slate-300 text-[11px]"></i>
                        @endif
                    </div>

                    <div class="sidebar-text min-w-0">
                        <p class="text-[12px] font-semibold text-white truncate">
                            {{ auth()->user()->name ?? 'Admin User' }}
                        </p>
                        <p class="text-[10px] text-slate-400 truncate">
                            {{ auth()->user()->email ?? 'admin@example.com' }}
                        </p>
                    </div>
                </div>

                @if(auth()->check() && auth()->user()->roles->count())
                    <div class="mt-1.5 flex flex-wrap gap-1 sidebar-text">
                        @foreach(auth()->user()->roles as $role)
                            <span
                                class="inline-flex px-1.5 py-0.5 rounded-full bg-[#ffd21f]/10 text-[#ffd21f] text-[9px] font-medium border border-[#ffd21f]/20">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-2.5 py-3 space-y-1 text-[13px]">
            @can('Dashboard_View')
                    <a href="{{ route('admin.dashboard') }}"
                        class="sidebar-link group flex items-center gap-2.5 px-2.5 py-2 rounded-[12px] border transition-all duration-200
                            {{ request()->routeIs('admin.dashboard')
                ? 'bg-white/10 border-white/15 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] text-white'
                : 'border-transparent text-slate-300 hover:bg-white/5 hover:border-white/10 hover:text-white' }}">

                        <span class="sidebar-icon w-8 h-8 border border-white/5 rounded-md flex items-center justify-center shrink-0
                                {{ request()->routeIs('admin.dashboard')
                ? 'bg-[#1a2438] text-[#f5d86c]'
                : 'bg-[#10192b] text-slate-400 group-hover:text-white' }}">
                            <i class="fas fa-gauge-high text-[11px]"></i>
                        </span>

                        <span class="sidebar-text text-[12px] font-medium">Dashboard</span>
                    </a>
            @endcan

            @canany(['User_ViewAll', 'User_ViewMine', 'User_Add', 'User_View', 'User_Edit', 'User_Delete', 'User_Revoke'])
                    @php
                        $userMenuOpen = request()->routeIs('admin.users') || request()->routeIs('admin.users.*') || request()->routeIs('admin.my-users');
                    @endphp

                    <div class="sidebar-group">
                        <button type="button"
                            class="sidebar-collapse-btn group w-full flex items-center gap-2.5 px-2.5 py-2 rounded-[12px] border transition-all duration-200
                                {{ $userMenuOpen
                ? 'bg-white/10 border-white/15 text-white'
                : 'border-transparent text-slate-300 hover:bg-white/5 hover:border-white/10 hover:text-white' }}"
                            data-target="userManagementMenu" aria-expanded="{{ $userMenuOpen ? 'true' : 'false' }}">

                            <span
                                class="sidebar-icon w-8 h-8 border border-white/5 rounded-md flex items-center justify-center shrink-0
                                    {{ $userMenuOpen ? 'bg-[#1a2438] text-[#f5d86c]' : 'bg-[#10192b] text-slate-400 group-hover:text-white' }}">
                                <i class="fas fa-users text-[11px]"></i>
                            </span>

                            <span class="sidebar-text flex-1 text-left text-[12px] font-medium">Users</span>

                            <svg class="sidebar-arrow w-3 h-3 transition-transform duration-300 {{ $userMenuOpen ? 'rotate-180' : '' }}"
                                data-arrow="userManagementMenu" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="userManagementMenu" class="sidebar-submenu mt-1 space-y-1 {{ $userMenuOpen ? '' : 'hidden' }}">
                            @can('User_ViewAll')
                                        <a href="{{ route('admin.users') }}" class="sidebar-sublink ml-2 flex items-center gap-2 px-2.5 py-1.5 rounded-[10px] transition
                                                    {{ request()->routeIs('admin.users')
                                ? 'bg-[#111b2e] text-[#f5d86c]'
                                : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                            <span
                                                class="w-7 h-7 rounded-md bg-[#10192b] flex items-center justify-center shrink-0 border border-white/5">
                                                <i class="fas fa-list text-[10px]"></i>
                                            </span>
                                            <span class="sidebar-text text-[11px] font-medium">All Users</span>
                                        </a>
                            @endcan

                            @can('User_ViewMine')
                                        <a href="{{ route('admin.my-users') }}" class="sidebar-sublink ml-2 flex items-center gap-2 px-2.5 py-1.5 rounded-[10px] transition
                                                    {{ request()->routeIs('admin.my-users')
                                ? 'bg-[#111b2e] text-[#f5d86c]'
                                : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                            <span
                                                class="w-7 h-7 rounded-md bg-[#10192b] flex items-center justify-center shrink-0 border border-white/5">
                                                <i class="fas fa-user text-[10px]"></i>
                                            </span>
                                            <span class="sidebar-text text-[11px] font-medium">My Users</span>
                                        </a>
                            @endcan

                            @can('User_Add')
                                        <a href="{{ route('admin.users.create') }}" class="sidebar-sublink ml-2 flex items-center gap-2 px-2.5 py-1.5 rounded-[10px] transition
                                                    {{ request()->routeIs('admin.users.create')
                                ? 'bg-[#111b2e] text-[#f5d86c]'
                                : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                            <span
                                                class="w-7 h-7 rounded-md bg-[#10192b] flex items-center justify-center shrink-0 border border-white/5">
                                                <i class="fas fa-user-plus text-[10px]"></i>
                                            </span>
                                            <span class="sidebar-text text-[11px] font-medium">Add User</span>
                                        </a>
                            @endcan
                        </div>
                    </div>
            @endcanany
        </nav>

        <!-- Logout -->
        <div class="px-2.5 pb-3 pt-2 border-t border-white/10">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="sidebar-link w-full flex items-center gap-2.5 px-2.5 py-2 rounded-[12px] border border-red-500/20 bg-red-500/5 text-red-300 hover:bg-red-500 hover:text-white transition-all duration-200">
                    <span
                        class="sidebar-icon w-8 h-8 rounded-md bg-[#2a1220] flex items-center justify-center shrink-0">
                        <i class="fas fa-right-from-bracket text-[11px]"></i>
                    </span>
                    <span class="sidebar-text text-[12px] font-medium">Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>