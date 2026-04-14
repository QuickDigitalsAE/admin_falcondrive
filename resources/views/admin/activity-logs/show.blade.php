@extends('admin.layouts.app')

@section('title', 'Activity Log Detail')
@section('page_title', 'Activity Log Detail')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.activity-logs') }}" class="transition hover:text-[#9b7a28]">Activity Logs</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Log #{{ $log->id }}</span>
    </nav>
@endsection

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-5xl">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Activity Log</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">{{ $meta['summary'] }}</h1>
                            <p class="mt-1 text-sm text-slate-500">Clear breakdown of what happened, who performed it, and when.</p>
                        </div>
                        <a href="{{ route('admin.activity-logs') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                    </div>
                </div>

                <div class="grid gap-4 p-4 sm:p-6 lg:grid-cols-2">
                    <div class="rounded-[22px] border border-[#eadfbe] bg-[#fffdf8] p-5">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Overview</p>
                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                            <p><span class="font-semibold text-slate-800">Log ID:</span> {{ $log->id }}</p>
                            <p><span class="font-semibold text-slate-800">Category:</span> {{ ucfirst($meta['category']) }}</p>
                            <p><span class="font-semibold text-slate-800">Action:</span> {{ $meta['action_label'] }}</p>
                            <p><span class="font-semibold text-slate-800">Module:</span> {{ $meta['module_label'] }}</p>
                            <p><span class="font-semibold text-slate-800">Performed By:</span> {{ $meta['performed_by'] }}</p>
                            <p><span class="font-semibold text-slate-800">Email:</span> {{ $meta['performed_by_email'] ?: 'N/A' }}</p>
                            <p><span class="font-semibold text-slate-800">Time:</span> {{ $meta['created_at_human'] }}</p>
                        </div>
                    </div>

                    <div class="rounded-[22px] border border-[#eadfbe] bg-[#fffdf8] p-5">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Details</p>
                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                            @forelse ($meta['details'] as $detail)
                                <div class="rounded-2xl border border-[#f1e7ce] bg-white px-4 py-3">{{ $detail }}</div>
                            @empty
                                <div class="rounded-2xl border border-[#f1e7ce] bg-white px-4 py-3 text-slate-400">No additional details available.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="border-t border-[#f0e6ca] p-4 sm:p-6">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Raw Changes</p>
                    <pre class="mt-4 overflow-auto rounded-[22px] border border-[#eadfbe] bg-[#fffdf8] p-4 text-xs leading-6 text-slate-700">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
    </section>
@endsection
