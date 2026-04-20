@extends('admin.layouts.app')

@section('title', 'Promotion Details')
@section('page_title', 'Promotion Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500"><a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a><i class="fas fa-chevron-right text-[10px] text-slate-400"></i><a href="{{ route('admin.promotions') }}" class="transition hover:text-[#9b7a28]">Promotions</a><i class="fas fa-chevron-right text-[10px] text-slate-400"></i><span class="font-medium text-slate-700">Promotion Details</span></nav>
@endsection

@section('content')
<section class="w-full pb-8">
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
            <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Promotions Management</p>
                        <h1 class="text-[28px] font-bold leading-tight text-slate-900">Promotion Details</h1>
                        <p class="mt-1 text-sm text-slate-500">Detailed view of the selected promotion.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @can('Promotion_Edit')
                            @if (is_null($promotion->deleted_at))
                                <a href="{{ route('admin.promotions.edit', $promotion->id) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>Edit Promotion</a>
                            @endif
                        @endcan
                        <a href="{{ route('admin.promotions') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-6">
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <div class="xl:col-span-1">
                        <div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-5">
                            <div class="flex flex-col items-center text-center">
                                <div class="h-40 w-full overflow-hidden rounded-3xl border border-[#eadfbe] bg-white shadow-sm ring-4 ring-[#fbf2d6]"><img src="{{ $promotion->image_url ?: 'https://placehold.co/800x500/f8e8b2/5e450a?text=Promotion' }}" alt="{{ $promotion->name_en }}" class="h-full w-full object-cover"></div>
                                <h2 class="mt-4 text-xl font-bold text-slate-900">{{ $promotion->name_en }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ $promotion->name_ar }}</p>
                                <div class="mt-4 flex flex-wrap justify-center gap-2"><span class="inline-flex items-center rounded-full bg-[#f8edd0] px-3 py-1 text-xs font-semibold text-[#8b6717] ring-1 ring-[#ecdca8]">{{ $promotion->slug }}</span>@if($promotion->top_offer)<span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">Top Offer</span>@endif</div>
                            </div>
                        </div>
                    </div>
                    <div class="xl:col-span-2 space-y-6">
                        <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                            <div class="mb-5"><h3 class="text-lg font-semibold text-slate-900">Promotion Information</h3><p class="mt-1 text-sm text-slate-500">View bilingual content and SEO information.</p></div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Name EN</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $promotion->name_en ?: 'N/A' }}</p></div>
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Name AR</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $promotion->name_ar ?: 'N/A' }}</p></div>
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4 sm:col-span-2"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Slug</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $promotion->slug ?: 'N/A' }}</p></div>
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">SEO Title EN</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $promotion->seo_title_en ?: 'N/A' }}</p></div>
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">SEO Title AR</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $promotion->seo_title_ar ?: 'N/A' }}</p></div>
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4 sm:col-span-2"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">SEO Brief EN</p><p class="mt-2 whitespace-pre-line text-sm text-slate-900">{{ $promotion->seo_brief_en ?: 'N/A' }}</p></div>
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4 sm:col-span-2"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">SEO Brief AR</p><p class="mt-2 whitespace-pre-line break-words text-right text-sm leading-8 text-slate-900 [overflow-wrap:anywhere]" dir="rtl">{{ $promotion->seo_brief_ar ?: 'N/A' }}</p></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                            <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm"><h3 class="text-sm font-semibold text-slate-900">Description EN</h3><div class="prose prose-sm mt-4 max-w-none text-slate-600">{!! $promotion->description_en ?: '<p class="text-slate-400">No English description added.</p>' !!}</div></div>
                            <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm"><h3 class="text-sm font-semibold text-slate-900">Description AR</h3><div class="prose prose-sm mt-4 max-w-none break-words text-right leading-8 text-slate-600 [overflow-wrap:anywhere] [&_h1]:text-right [&_h2]:text-right [&_h3]:text-right [&_h4]:text-right [&_li]:text-right [&_ol]:pr-6 [&_p]:my-0 [&_p]:mb-4 [&_p]:leading-8 [&_strong]:font-semibold [&_ul]:pr-6" dir="rtl">{!! $promotion->description_ar ?: '<p class="text-slate-400">No Arabic description added.</p>' !!}</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
