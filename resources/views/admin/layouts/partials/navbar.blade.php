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
    <div class="px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-start justify-between gap-3">
            <!-- LEFT -->
            <div class="flex min-w-0 items-start gap-3">
                <button id="sidebarToggle"
                    class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#9b7a28] shadow-sm transition hover:bg-[#fff8e7] hover:text-[#7a5d18]">
                    <i class="fas fa-bars text-[16px]"></i>
                </button>

                <div class="min-w-0">

                    <h1 class="truncate text-[20px] font-semibold leading-tight text-slate-900">
                        {{ $pageTitle }}
                    </h1>

                    <div class="mt-2">
                        @hasSection('breadcrumbs')
                            {!! $pageBreadcrumbs !!}
                        @else
                            <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
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

            <!-- RIGHT -->
            <div class="flex shrink-0 items-center gap-2.5">
                <!-- Desktop Search -->
                <div class="hidden xl:flex w-[300px] items-center">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#b49543]">
                            <i class="fas fa-magnifying-glass text-[14px]"></i>
                        </span>

                        <input type="text" placeholder="Search..."
                            class="h-10 w-full rounded-full border border-[#eadfbe] bg-[#fffaf0] pl-10 pr-3 text-[13px] text-slate-700 placeholder:text-slate-400 shadow-sm transition focus:border-[#d8bf79] focus:bg-white focus:outline-none" />
                    </div>
                </div>

                <!-- Mobile Search -->
                <button
                    class="xl:hidden flex h-10 w-10 items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#9b7a28] shadow-sm transition hover:bg-[#fff8e7]">
                    <i class="fas fa-magnifying-glass text-[14px]"></i>
                </button>

                <!-- Notification -->
                <div class="relative" id="notificationDropdownWrapper">
                    <button id="notificationDropdownToggle"
                        class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#9b7a28] shadow-sm transition hover:bg-[#fff8e7]">
                        <i class="far fa-bell text-[15px]"></i>
                        <span class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-[#e0b63f] ring-2 ring-white"></span>
                    </button>

                    <div id="notificationDropdownMenu"
                        class="absolute right-0 top-[calc(100%+10px)] hidden w-[340px] overflow-hidden rounded-2xl border border-[#eadfbe] bg-white shadow-[0_18px_45px_rgba(15,23,42,0.12)]">
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

                <!-- Profile -->
                <div class="relative" id="profileDropdownWrapper">
                    <button id="profileDropdownToggle"
                        class="flex max-w-[200px] items-center gap-2 rounded-xl border border-[#eadfbe] bg-white px-2.5 py-1.5 shadow-sm transition hover:bg-[#fffaf2]">

                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f4dd70] text-slate-900">
                            <i class="fas fa-user text-[14px]"></i>
                        </span>

                        <span class="hidden min-w-0 text-left sm:block">
                            <span class="block truncate text-[13px] font-semibold text-slate-800">
                                {{ auth()->user()->name ?? 'Admin' }}
                            </span>
                            <span class="block truncate text-[11px] text-slate-500">
                                {{ auth()->user()->email ?? 'admin@falcondrive.ae' }}
                            </span>
                        </span>

                        <i class="fas fa-chevron-down ml-auto text-[11px] text-slate-400"></i>
                    </button>

                    <div id="profileDropdownMenu"
                        class="absolute right-0 top-[calc(100%+10px)] hidden w-[260px] overflow-hidden rounded-2xl border border-[#eadfbe] bg-white shadow-lg">

                        <div class="border-b border-[#f0e6ca] px-5 py-4 bg-[#fffaf0]">
                            <p class="text-[14px] font-semibold text-slate-900">
                                {{ auth()->user()->name ?? 'Admin User' }}
                            </p>
                            <p class="text-[12px] text-slate-500">
                                {{ auth()->user()->email ?? 'admin@falcondrive.ae' }}
                            </p>
                        </div>

                        <div class="py-2">
                            <a href="#" class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-[#fffaf2]">
                                <i class="far fa-user text-[14px] text-[#b49543]"></i>
                                <span class="text-[14px]">Profile</span>
                            </a>

                            <a href="#" class="flex items-center gap-3 px-5 py-3 text-slate-700 hover:bg-[#fffaf2]">
                                <i class="fas fa-gear text-[14px] text-[#b49543]"></i>
                                <span class="text-[14px]">Settings</span>
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

        <!-- Tablet Search -->
        <div class="mt-3 xl:hidden">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#b49543]">
                    <i class="fas fa-magnifying-glass text-[13px]"></i>
                </span>
                <input type="text" placeholder="Search..."
                    class="h-10 w-full rounded-xl border border-[#eadfbe] bg-[#fffaf0] pl-9 pr-3 text-[13px] shadow-sm focus:border-[#d8bf79] focus:bg-white focus:outline-none" />
            </div>
        </div>
    </div>
</header>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const notificationToggle = document.getElementById('notificationDropdownToggle');
            const notificationMenu = document.getElementById('notificationDropdownMenu');
            const notificationWrapper = document.getElementById('notificationDropdownWrapper');

            const profileToggle = document.getElementById('profileDropdownToggle');
            const profileMenu = document.getElementById('profileDropdownMenu');
            const profileWrapper = document.getElementById('profileDropdownWrapper');

            if (notificationToggle && notificationMenu) {
                notificationToggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    notificationMenu.classList.toggle('hidden');
                    if (profileMenu) profileMenu.classList.add('hidden');
                });
            }

            if (profileToggle && profileMenu) {
                profileToggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    profileMenu.classList.toggle('hidden');
                    if (notificationMenu) notificationMenu.classList.add('hidden');
                });
            }

            document.addEventListener('click', function (e) {
                if (notificationWrapper && !notificationWrapper.contains(e.target) && notificationMenu) {
                    notificationMenu.classList.add('hidden');
                }

                if (profileWrapper && !profileWrapper.contains(e.target) && profileMenu) {
                    profileMenu.classList.add('hidden');
                }
            });
        });
    </script>
@endpush