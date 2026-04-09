<header class="sticky top-0 z-20 bg-white border-b border-slate-200/80 backdrop-blur">
    <div class="px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between gap-3">

            <!-- LEFT -->
            <div class="flex items-center gap-3 min-w-0">

                <!-- Sidebar Toggle -->
                <button id="sidebarToggle"
                    class="w-11 h-11 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-900 flex items-center justify-center transition shadow-sm">
                    <i class="fas fa-bars text-[16px]"></i>
                </button>

                <!-- Title -->
                <div class="min-w-0">
                    <p class="text-[10px] uppercase tracking-[0.25em] text-slate-400 mb-0.5 truncate">
                        FalconDrive CMS
                    </p>

                    <h1 class="text-[20px] font-semibold text-slate-900 leading-tight truncate">
                        @yield('page_title', 'Dashboard')
                    </h1>

                    <p class="text-[13px] text-slate-500 truncate">
                        @yield('page_subtitle', 'Welcome back to FalconDrive admin panel')
                    </p>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="flex items-center gap-2.5 shrink-0">

                <!-- Desktop Search -->
                <div class="hidden xl:flex items-center w-[300px]">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fas fa-magnifying-glass text-[14px]"></i>
                        </span>

                        <input type="text" placeholder="Search..."
                            class="w-full h-10 rounded-full border border-slate-200 bg-slate-50 pl-10 pr-3 text-[13px] text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-slate-300 focus:bg-white transition shadow-sm" />
                    </div>
                </div>

                <!-- Mobile Search -->
                <button
                    class="xl:hidden w-10 h-10 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 flex items-center justify-center shadow-sm">
                    <i class="fas fa-magnifying-glass text-[14px]"></i>
                </button>

                <!-- Notification -->
                <button
                    class="relative w-10 h-10 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 flex items-center justify-center shadow-sm">
                    <i class="far fa-bell text-[15px]"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-amber-400 rounded-full"></span>
                </button>

                <!-- Profile -->
                <div class="relative" id="profileDropdownWrapper">
                    <button id="profileDropdownToggle"
                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 hover:bg-slate-50 transition shadow-sm max-w-[200px]">

                        <span
                            class="w-9 h-9 rounded-full bg-[#f4dd70] text-slate-900 flex items-center justify-center shrink-0">
                            <i class="fas fa-user text-[14px]"></i>
                        </span>

                        <span class="min-w-0 text-left hidden sm:block">
                            <span class="block text-[13px] font-semibold text-slate-800 truncate">
                                {{ auth()->user()->name ?? 'Admin' }}
                            </span>
                            <span class="block text-[11px] text-slate-500 truncate">
                                {{ auth()->user()->email ?? 'admin@falcondrive.ae' }}
                            </span>
                        </span>

                        <i class="fas fa-chevron-down text-[11px] text-slate-400 ml-auto"></i>
                    </button>

                    <!-- Dropdown -->
                    <div id="profileDropdownMenu"
                        class="absolute right-0 top-[calc(100%+10px)] w-[260px] rounded-2xl border border-slate-200 bg-white shadow-lg overflow-hidden hidden">

                        <div class="px-5 py-4 border-b border-slate-200">
                            <p class="text-[14px] font-semibold text-slate-900">
                                {{ auth()->user()->name ?? 'Admin User' }}
                            </p>
                            <p class="text-[12px] text-slate-500">
                                {{ auth()->user()->email ?? 'admin@falcondrive.ae' }}
                            </p>
                        </div>

                        <div class="py-2">
                            <a href="#" class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50">
                                <i class="far fa-user text-[14px]"></i>
                                <span class="text-[14px]">Profile</span>
                            </a>

                            <a href="#" class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-slate-50">
                                <i class="fas fa-gear text-[14px]"></i>
                                <span class="text-[14px]">Settings</span>
                            </a>

                            <form action="{{ route('admin.logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-5 py-3 text-red-500 hover:bg-red-50 text-left">
                                    <i class="fas fa-right-from-bracket text-[14px]"></i>
                                    <span class="text-[14px]">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Tablet Search -->
        <div class="mt-3 xl:hidden">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fas fa-magnifying-glass text-[13px]"></i>
                </span>
                <input type="text" placeholder="Search..."
                    class="w-full h-10 rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-[13px] focus:outline-none focus:border-slate-300 focus:bg-white shadow-sm" />
            </div>
        </div>
    </div>
</header>