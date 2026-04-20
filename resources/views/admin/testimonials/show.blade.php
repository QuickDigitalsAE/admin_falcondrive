@extends('admin.layouts.app')
@section('title', 'Testimonial Details')
@section('page_title', 'Testimonial Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.testimonials') }}" class="transition hover:text-[#9b7a28]">Testimonials</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Testimonial Details</span>
    </nav>
@endsection

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl space-y-6">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0"><p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Testimonials Management</p><h1 class="text-[28px] font-bold leading-tight text-slate-900">Testimonial Details</h1><p class="mt-1 text-sm text-slate-500">Detailed view of the selected testimonial.</p></div>
                        <div class="flex flex-wrap gap-3">@if(is_null($testimonial->deleted_at))<a href="{{ route('admin.testimonials.edit', $testimonial->id) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>Edit Testimonial</a>@endif<a href="{{ route('admin.testimonials') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a></div>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                        <div class="xl:col-span-1"><div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-5"><div class="flex flex-col items-center text-center"><div class="h-28 w-28 overflow-hidden rounded-3xl border border-[#eadfbe] bg-white shadow-sm ring-4 ring-[#fbf2d6]"><img src="{{ $testimonial->image_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($testimonial->name_en ?? 'Testimonial') . '&background=F8E8B2&color=5E450A&size=200' }}" alt="{{ $testimonial->name_en }}" class="h-full w-full object-cover"></div><h2 class="mt-4 text-xl font-bold text-slate-900">{{ $testimonial->name_en }}</h2><p class="mt-1 text-sm text-slate-500">{{ $testimonial->name_ar }}</p></div></div></div>
                        <div class="xl:col-span-2"><div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm"><div class="grid grid-cols-1 gap-4"><div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Description EN</p><p class="mt-2 whitespace-pre-line text-sm font-semibold text-slate-900">{{ $testimonial->description_en }}</p></div><div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Description AR</p><p class="mt-2 whitespace-pre-line break-words text-right text-sm font-semibold leading-8 text-slate-900 [overflow-wrap:anywhere]" dir="rtl">{{ $testimonial->description_ar }}</p></div></div></div></div>
                    </div>
                    <div class="mt-6 flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">@if(is_null($testimonial->deleted_at))<a href="{{ route('admin.testimonials.edit', $testimonial->id) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>Edit Testimonial</a>@endif<a href="{{ route('admin.testimonials') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-list mr-2 text-[13px]"></i>All Testimonials</a></div>
                </div>
            </div>
        </div>
    </section>
@endsection
