@extends('admin.layouts.app')

@section('title', 'Highlight Details')
@section('page_title', 'Highlight Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500"><a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a><i class="fas fa-chevron-right text-[10px] text-slate-400"></i><a href="{{ route('admin.highlights') }}" class="transition hover:text-[#9b7a28]">Highlights</a><i class="fas fa-chevron-right text-[10px] text-slate-400"></i><span class="font-medium text-slate-700">Highlight Details</span></nav>
@endsection

@section('content')
    <section class="w-full pb-8">
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
            <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Highlights Management</p>
                        <h1 class="text-[28px] font-bold leading-tight text-slate-900">Highlight Details</h1>
                        <p class="mt-1 text-sm text-slate-500">Detailed view of the selected highlight.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @can('Highlight_Edit')
                            @if (is_null($highlight->deleted_at))
                                <a href="{{ route('admin.highlights.edit', $highlight->id) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>Edit Highlight</a>
                            @endif
                        @endcan
                        <a href="{{ route('admin.highlights') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-6">
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <div class="xl:col-span-1">
                        <div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-5">
                            <div class="flex flex-col items-center text-center">
                                <div class="h-40 w-full overflow-hidden rounded-3xl border border-[#eadfbe] bg-white shadow-sm ring-4 ring-[#fbf2d6]"><img src="{{ $highlight->image_url ?: 'https://placehold.co/800x500/f8e8b2/5e450a?text=Highlight' }}" alt="{{ $highlight->title_en }}" class="h-full w-full object-cover"></div>
                                <h2 class="mt-4 text-xl font-bold text-slate-900">{{ $highlight->title_en }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ $highlight->title_ar }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-2 space-y-6">
                        <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                            <div class="mb-5"><h3 class="text-lg font-semibold text-slate-900">Highlight Information</h3><p class="mt-1 text-sm text-slate-500">View bilingual title and media information.</p></div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Title EN</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $highlight->title_en ?: 'N/A' }}</p></div>
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Title AR</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $highlight->title_ar ?: 'N/A' }}</p></div>
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4 sm:col-span-2"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">URL</p><p class="mt-2 break-all text-sm font-semibold text-slate-900">@if ($highlight->url)<a href="{{ $highlight->url }}" target="_blank" rel="noopener noreferrer" class="text-[#9b7a28] hover:text-[#7d6220]">{{ $highlight->url }}</a>@else N/A @endif</p></div>
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Created At</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ optional($highlight->created_at)->format('d M Y, h:i A') ?: 'N/A' }}</p></div>
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Updated At</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ optional($highlight->updated_at)->format('d M Y, h:i A') ?: 'N/A' }}</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>
    @include('admin.layouts.partials.super-admin-audit-card', ['record' => $highlight])
@endsection
