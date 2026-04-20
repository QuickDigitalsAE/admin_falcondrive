@extends('admin.layouts.app')

@section('page_title', 'Global Search')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-500 sm:gap-2 sm:text-[12px]">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Global Search</span>
    </nav>
@endsection

@section('content')
    <section class="space-y-6">
        <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-gradient-to-br from-[#fffdf8] to-[#fffaf0] shadow-[0_18px_46px_rgba(15,23,42,0.06)]">
            <div class="border-b border-[#f0e6ca] px-5 py-5 sm:px-6">
                <p class="text-[11px] uppercase tracking-[0.28em] text-[#b89a4c]">General Search</p>
                <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Global Search</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Search across allowed admin modules and jump directly to view or edit actions.
                        </p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-[#eadfbe] bg-white px-4 py-3 text-center shadow-sm">
                            <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Query</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $searchQuery !== '' ? $searchQuery : 'No query' }}</p>
                        </div>
                        <div class="rounded-2xl border border-[#eadfbe] bg-white px-4 py-3 text-center shadow-sm">
                            <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Results</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $results['total_results'] }}</p>
                        </div>
                        <div class="rounded-2xl border border-[#eadfbe] bg-white px-4 py-3 text-center shadow-sm">
                            <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Modules</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ count($results['groups']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-5 py-5 sm:px-6">
                <form method="GET" action="{{ route('admin.global-search') }}" class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#b49543]">
                        <i class="fas fa-magnifying-glass text-[15px]"></i>
                    </span>
                    <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Search users, cars, settings, promotions..."
                        class="h-14 w-full rounded-[22px] border border-[#eadfbe] bg-white pl-12 pr-32 text-[15px] text-slate-800 shadow-sm outline-none transition focus:border-[#d8bf79] focus:ring-4 focus:ring-[#f7e9b5]">
                    <button type="submit"
                        class="absolute right-2 top-2 inline-flex h-10 items-center justify-center rounded-2xl bg-gradient-to-r from-[#e0bc5a] to-[#bb8c26] px-5 text-sm font-semibold text-white shadow-[0_10px_24px_rgba(187,140,38,0.28)] transition hover:opacity-95">
                        Search
                    </button>
                </form>
            </div>
        </div>

        @if (!empty($results['quick_links']))
            <div class="rounded-[24px] border border-[#eadfbe] bg-white px-5 py-5 shadow-sm sm:px-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Quick Access</p>
                        <h3 class="mt-1 text-lg font-semibold text-slate-900">Matched Modules</h3>
                    </div>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($results['quick_links'] as $quickLink)
                        <a href="{{ $quickLink['url'] }}"
                            class="group flex items-center gap-3 rounded-[20px] border border-[#eadfbe] bg-gradient-to-br from-[#fffdf8] to-[#fffaf0] px-4 py-4 transition hover:-translate-y-0.5 hover:border-[#d7be73] hover:shadow-[0_14px_32px_rgba(15,23,42,0.08)]">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#fff1c8] text-[#9b7a28] ring-1 ring-[#f1ddaa]">
                                <i class="fas {{ $quickLink['icon'] }} text-[14px]"></i>
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $quickLink['label'] }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">Open module</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($searchQuery === '')
            <div class="rounded-[24px] border border-dashed border-[#dfcf9b] bg-[#fffaf0] px-6 py-10 text-center text-slate-600 shadow-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#fff1c8] text-[#a67d20] ring-1 ring-[#f1ddaa]">
                    <i class="fas fa-magnifying-glass text-xl"></i>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900">Start with a keyword</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Search across the modules you are allowed to access. Results only include permitted records and actions.
                </p>
            </div>
        @elseif (empty($results['groups']))
            <div class="rounded-[24px] border border-dashed border-[#dfcf9b] bg-[#fffaf0] px-6 py-10 text-center text-slate-600 shadow-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#fff1c8] text-[#a67d20] ring-1 ring-[#f1ddaa]">
                    <i class="fas fa-folder-open text-xl"></i>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900">No matches found</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Try another keyword or search by name, email, slug, title, or setting key.
                </p>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($results['groups'] as $group)
                    <section class="overflow-hidden rounded-[24px] border border-[#eadfbe] bg-white shadow-sm">
                        <div class="flex flex-col gap-3 border-b border-[#f0e6ca] bg-[#fffaf0] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#fff1c8] text-[#9b7a28] ring-1 ring-[#f1ddaa]">
                                    <i class="fas {{ $group['icon'] }} text-[14px]"></i>
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">{{ $group['label'] }}</h3>
                                    <p class="text-sm text-slate-500">{{ count($group['items']) }} matching result(s)</p>
                                </div>
                            </div>
                            <a href="{{ $group['index_url'] }}"
                                class="inline-flex items-center justify-center rounded-xl border border-[#eadfbe] bg-white px-4 py-2 text-sm font-semibold text-[#7d6220] transition hover:bg-[#fff8e8]">
                                Open Module
                            </a>
                        </div>

                        <div class="divide-y divide-[#f3ecd5]">
                            @foreach ($group['items'] as $item)
                                <div class="flex flex-col gap-4 px-5 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="truncate text-[15px] font-semibold text-slate-900">{{ $item['title'] }}</h4>
                                            <span class="inline-flex rounded-full bg-[#f7edd0] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-[#8a6a1c]">
                                                {{ $item['badge'] }}
                                            </span>
                                        </div>
                                        @if ($item['subtitle'])
                                            <p class="mt-1 text-sm text-slate-600">{{ $item['subtitle'] }}</p>
                                        @endif
                                        @if ($item['meta'])
                                            <p class="mt-1 text-xs text-slate-500">{{ $item['meta'] }}</p>
                                        @endif
                                    </div>

                                    @if (!empty($item['actions']))
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($item['actions'] as $action)
                                                <a href="{{ $action['url'] }}"
                                                    class="inline-flex items-center gap-2 rounded-xl border border-[#eadfbe] bg-[#fffaf0] px-4 py-2 text-sm font-semibold text-[#7d6220] transition hover:bg-[#fff3d9]">
                                                    <i class="fas {{ $action['icon'] }} text-[12px]"></i>
                                                    <span>{{ $action['label'] }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </section>
@endsection

