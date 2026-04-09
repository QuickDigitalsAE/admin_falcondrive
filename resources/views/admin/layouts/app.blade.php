<!DOCTYPE html>
<html lang="en">
@include('admin.layouts.partials.head')

<body class="bg-slate-100 text-slate-800 antialiased">
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-30 hidden lg:hidden"></div>

    <div class="min-h-screen flex">
        @include('admin.layouts.partials.sidebar')

        <div id="mainContent" class="flex-1 min-w-0 flex flex-col h-screen">
            @include('admin.layouts.partials.navbar')

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
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