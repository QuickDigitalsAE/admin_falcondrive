<!DOCTYPE html>
<html lang="en">
@include('admin.layouts.partials.head')

<body class="m-0 h-screen overflow-hidden bg-[#f8f7f2] text-slate-800 antialiased">
    <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/50 lg:hidden"></div>

    <div class="flex h-screen w-full overflow-hidden">
        @include('admin.layouts.partials.sidebar')

        <div id="mainContent" class="flex h-screen min-w-0 flex-1 flex-col overflow-hidden transition-all duration-300">
            @include('admin.layouts.partials.navbar')

            <main id="pageMain" class="flex-1 overflow-y-auto overflow-x-hidden bg-[#f7f5ee] p-3 sm:p-5 lg:p-7">
                <div class="min-h-full">
                    @yield('content')
                </div>
            </main>

            <div class="shrink-0">
                @include('admin.layouts.partials.footer')
            </div>
        </div>
    </div>

    @include('admin.layouts.partials.modals')
    @include('admin.layouts.partials.scripts')
    @stack('scripts')
</body>
</html>