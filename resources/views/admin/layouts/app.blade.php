<!DOCTYPE html>
<html lang="en">
@include('admin.layouts.partials.head')

<body class="bg-[#f8f7f2] text-slate-800 antialiased">
    <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/50 lg:hidden"></div>

    <div class="flex h-screen">
        @include('admin.layouts.partials.sidebar')

        <div id="mainContent" class="flex h-screen min-w-0 flex-1 flex-col overflow-y-auto">
            @include('admin.layouts.partials.navbar')

            <main class="flex-1 overflow-y-auto bg-[#f7f5ee] p-4 sm:p-6 lg:p-7">
                @yield('content')
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