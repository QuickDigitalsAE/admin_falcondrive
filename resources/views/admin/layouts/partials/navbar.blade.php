@php
    $pageTitle = trim($__env->yieldContent('page_title', 'Dashboard'));
    $pageBreadcrumbs = $__env->yieldContent('breadcrumbs');

    $notifications = [
        [
            'title' => 'New user registered',
            'message' => 'A new admin user account was created.',
            'time' => '2 min ago',
            'icon' => 'fa-user-plus',
            'color' => 'amber',
            'url' => route('admin.users'),
        ],
        [
            'title' => 'Password reset requested',
            'message' => 'A password recovery action was triggered.',
            'time' => '18 min ago',
            'icon' => 'fa-key',
            'color' => 'blue',
            'url' => route('admin.users'),
        ],
        [
            'title' => 'Profile updated',
            'message' => 'System profile details were recently changed.',
            'time' => '1 hour ago',
            'icon' => 'fa-user-gear',
            'color' => 'emerald',
            'url' => route('admin.users'),
        ],
    ];
@endphp

<header class="sticky top-0 z-20 border-b border-[#eadfbe] bg-[#fcfbf7]/95 backdrop-blur">
    <div class="px-3 py-3 sm:px-4 md:px-5 lg:px-8">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                <button id="sidebarToggle"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#9b7a28] shadow-sm transition hover:bg-[#fff8e7] hover:text-[#7a5d18] sm:h-11 sm:w-11">
                    <i class="fas fa-bars text-[16px]"></i>
                </button>

                <div class="min-w-0">
                    <h1 class="truncate text-[18px] font-semibold leading-tight text-slate-900 sm:text-[20px]">
                        {{ $pageTitle }}
                    </h1>

                    <div class="mt-1.5 sm:mt-2">
                        @hasSection('breadcrumbs')
                            {!! $pageBreadcrumbs !!}
                        @else
                            <nav class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-500 sm:gap-2 sm:text-[12px]">
                                <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
                                    <i class="fas fa-house text-[11px]"></i>
                                    <span class="ml-1">Dashboard</span>
                                </a>
                                <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
                                <span class="font-medium text-slate-700">{{ $pageTitle }}</span>
                            </nav>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex w-full flex-wrap items-center justify-end gap-2 sm:gap-2.5 md:w-auto md:shrink-0 md:flex-nowrap">
                <div class="relative hidden xl:flex w-[280px] items-center 2xl:w-[300px]" id="globalSearchDesktopWrapper">
                    <form action="{{ route('admin.global-search') }}" method="GET" class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#b49543]">
                            <i class="fas fa-magnifying-glass text-[14px]"></i>
                        </span>

                        <input id="globalSearchInput" type="text" name="q" autocomplete="off" placeholder="Search users, cars, settings..."
                            value="{{ request('q') }}"
                            data-search-endpoint="{{ route('admin.global-search.suggest') }}"
                            data-search-page="{{ route('admin.global-search') }}"
                            class="h-10 w-full rounded-full border border-[#eadfbe] bg-[#fffaf0] pl-10 pr-3 text-[13px] text-slate-700 placeholder:text-slate-400 shadow-sm transition focus:border-[#d8bf79] focus:bg-white focus:outline-none" />
                    </form>

                    <div id="globalSearchDropdown"
                        class="absolute right-0 top-[calc(100%+10px)] z-40 hidden w-[420px] max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-[24px] border border-[#eadfbe] bg-white shadow-[0_22px_52px_rgba(15,23,42,0.14)]">
                    </div>
                </div>

                <button
                    id="mobileSearchToggle"
                    class="xl:hidden flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#9b7a28] shadow-sm transition hover:bg-[#fff8e7]">
                    <i class="fas fa-magnifying-glass text-[14px]"></i>
                </button>

                <div class="relative" id="notificationDropdownWrapper">
                    <button id="notificationDropdownToggle"
                        class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#9b7a28] shadow-sm transition hover:bg-[#fff8e7]">
                        <i class="far fa-bell text-[15px]"></i>
                        <span class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-[#e0b63f] ring-2 ring-white"></span>
                    </button>

                    <div id="notificationDropdownMenu"
                        class="absolute right-0 top-[calc(100%+10px)] z-40 hidden w-[340px] max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-2xl border border-[#eadfbe] bg-white shadow-[0_18px_45px_rgba(15,23,42,0.12)]">
                        <div class="border-b border-[#f0e6ca] bg-[#fffaf0] px-4 py-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[14px] font-semibold text-slate-900">Notifications</p>
                                    <p class="text-[12px] text-slate-500">Recent system activity</p>
                                </div>
                                <button type="button"
                                    class="rounded-lg px-2 py-1 text-[11px] font-semibold text-[#a47d1f] transition hover:bg-[#fff3cf]">
                                    Mark all read
                                </button>
                            </div>
                        </div>

                        <div class="max-h-[360px] overflow-y-auto">
                            @forelse($notifications as $notification)
                                <a href="{{ $notification['url'] }}"
                                    class="flex items-start gap-3 border-b border-slate-100 px-4 py-3 transition hover:bg-[#fffaf2]">
                                    <span
                                        class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                                        {{ $notification['color'] === 'amber' ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $notification['color'] === 'blue' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $notification['color'] === 'emerald' ? 'bg-emerald-100 text-emerald-700' : '' }}">
                                        <i class="fas {{ $notification['icon'] }} text-[13px]"></i>
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span class="block text-[13px] font-semibold text-slate-800">
                                            {{ $notification['title'] }}
                                        </span>
                                        <span class="mt-0.5 block text-[12px] leading-5 text-slate-500">
                                            {{ $notification['message'] }}
                                        </span>
                                        <span class="mt-1.5 block text-[11px] font-medium text-[#b49543]">
                                            {{ $notification['time'] }}
                                        </span>
                                    </span>
                                </a>
                            @empty
                                <div class="px-4 py-10 text-center text-sm text-slate-500">
                                    No notifications available.
                                </div>
                            @endforelse
                        </div>

                        <div class="bg-[#fffaf0] px-4 py-3 text-center">
                            <a href="#"
                                class="inline-flex items-center text-[12px] font-semibold text-[#9b7a28] transition hover:opacity-80">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>

                <div class="relative min-w-0" id="profileDropdownWrapper">
                    <button id="profileDropdownToggle"
                        class="flex max-w-[58px] items-center gap-2 rounded-xl border border-[#eadfbe] bg-white px-2 py-1.5 shadow-sm transition hover:bg-[#fffaf2] sm:max-w-[200px] sm:px-2.5">

                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f4dd70] text-slate-900">
                            <i class="fas fa-user text-[14px]"></i>
                        </span>

                        <span class="hidden min-w-0 text-left sm:block lg:max-w-[130px] xl:max-w-none">
                            <span class="block truncate text-[13px] font-semibold text-slate-800">
                                {{ auth()->user()->name ?? 'Admin' }}
                            </span>
                            <span class="block truncate text-[11px] text-slate-500">
                                {{ auth()->user()->email ?? 'admin@falcondrive.ae' }}
                            </span>
                        </span>

                        <i id="profileDropdownChevron"
                            class="fas fa-chevron-down ml-auto hidden text-[11px] text-slate-400 transition-transform duration-200 sm:block"></i>
                    </button>

                    <div id="profileDropdownMenu"
                        class="absolute right-0 top-[calc(100%+10px)] z-40 hidden w-72 max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-2xl border border-[#eadfbe] bg-white shadow-lg sm:w-[260px]">

                        <div class="border-b border-[#f0e6ca] px-5 py-4 bg-[#fffaf0]">
                            <p class="text-[14px] font-semibold text-slate-900">
                                {{ auth()->user()->name ?? 'Admin User' }}
                            </p>
                            <p class="break-all text-[12px] text-slate-500">
                                {{ auth()->user()->email ?? 'admin@falcondrive.ae' }}
                            </p>
                        </div>

                        <div class="py-2">
                            <a href="{{ route('admin.account.profile') }}"
                                class="flex items-center gap-3 px-5 py-3 text-slate-700 transition hover:bg-[#fffaf2] {{ request()->routeIs('admin.account.profile') ? 'bg-[#fff8e8] text-[#7d6220]' : '' }}">
                                <i class="far fa-user text-[14px] text-[#b49543]"></i>
                                <span class="text-[14px]">Profile</span>
                            </a>

                            <a href="{{ route('admin.account.settings') }}"
                                class="flex items-center gap-3 px-5 py-3 text-slate-700 transition hover:bg-[#fffaf2] {{ request()->routeIs('admin.account.settings') ? 'bg-[#fff8e8] text-[#7d6220]' : '' }}">
                                <i class="fas fa-gear text-[14px] text-[#b49543]"></i>
                                <span class="text-[14px]">Change Password</span>
                            </a>

                            <form action="{{ route('admin.logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center gap-3 px-5 py-3 text-left text-red-500 hover:bg-red-50">
                                    <i class="fas fa-right-from-bracket text-[14px]"></i>
                                    <span class="text-[14px]">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="mobileSearchPanel" class="mt-3 hidden xl:hidden">
            <div class="relative" id="globalSearchMobileWrapper">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#b49543]">
                    <i class="fas fa-magnifying-glass text-[13px]"></i>
                </span>
                <form action="{{ route('admin.global-search') }}" method="GET">
                    <input id="mobileSearchInput" type="text" name="q" autocomplete="off" placeholder="Search users, cars, settings..."
                    value="{{ request('q') }}"
                    data-search-endpoint="{{ route('admin.global-search.suggest') }}"
                    data-search-page="{{ route('admin.global-search') }}"
                    class="h-10 w-full rounded-xl border border-[#eadfbe] bg-[#fffaf0] pl-9 pr-3 text-[13px] shadow-sm focus:border-[#d8bf79] focus:bg-white focus:outline-none" />
                </form>
                <div id="globalSearchMobileDropdown"
                    class="absolute left-0 right-0 top-[calc(100%+10px)] z-40 hidden overflow-hidden rounded-[24px] border border-[#eadfbe] bg-white shadow-[0_22px_52px_rgba(15,23,42,0.14)]">
                </div>
            </div>
        </div>
    </div>
</header>
