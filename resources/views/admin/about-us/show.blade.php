@extends('admin.layouts.app')

@section('title', 'About Us Details')
@section('page_title', 'About Us Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.about-us') }}" class="transition hover:text-[#9b7a28]">About Us</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Details</span>
    </nav>
@endsection

@section('content')
    <section class="space-y-5 pb-8">
        <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
            <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Content Details</p>
                        <h1 class="text-[28px] font-bold leading-tight text-slate-900">{{ $aboutUs->seo_title_en }}</h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Review the complete About Us content and metadata for this record.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @can('AboutUs_Edit')
                            @if(is_null($aboutUs->deleted_at))
                                <a href="{{ route('admin.about-us.edit', $aboutUs->id) }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]">
                                    <i class="fa-solid fa-pen mr-2 text-[13px]"></i>
                                    Edit
                                </a>
                            @endif
                        @endcan

                        <a href="{{ route('admin.about-us') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
                            <i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-4 sm:p-6 xl:grid-cols-2">
                <div class="rounded-3xl border border-[#f0e6ca] bg-[#fffdf8] p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-[#a98527]">SEO Metadata</h2>
                    <div class="mt-4 space-y-4 text-sm text-slate-600">
                        <div>
                            <p class="font-semibold text-slate-800">SEO Title EN</p>
                            <p class="mt-1">{{ $aboutUs->seo_title_en }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">SEO Title AR</p>
                            <p class="mt-1">{{ $aboutUs->seo_title_ar }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">SEO Brief EN</p>
                            <p class="mt-1 whitespace-pre-line">{{ $aboutUs->seo_brief_en }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">SEO Brief AR</p>
                            <p class="mt-1 whitespace-pre-line">{{ $aboutUs->seo_brief_ar }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#f0e6ca] bg-[#fffdf8] p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-[#a98527]">Audit Trail</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 text-sm text-slate-600">
                        <div>
                            <p class="font-semibold text-slate-800">Created By</p>
                            <p class="mt-1">{{ optional($aboutUs->createdByUser)->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">Updated By</p>
                            <p class="mt-1">{{ optional($aboutUs->updatedByUser)->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">Deleted By</p>
                            <p class="mt-1">{{ optional($aboutUs->deletedByUser)->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">Created At</p>
                            <p class="mt-1">{{ optional($aboutUs->created_at)->format('d M Y, h:i A') ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">Updated At</p>
                            <p class="mt-1">{{ optional($aboutUs->updated_at)->format('d M Y, h:i A') ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">Status</p>
                            <p class="mt-1">{{ $aboutUs->deleted_at ? 'Deleted' : 'Active' }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#f0e6ca] bg-[#fffdf8] p-5 xl:col-span-2">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-[#a98527]">First Section</h2>
                    <div class="mt-4 grid grid-cols-1 gap-5 xl:grid-cols-2">
                        <div>
                            <p class="font-semibold text-slate-800">English</p>
                            <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $aboutUs->first_section_en }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">Arabic</p>
                            <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $aboutUs->first_section_ar }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#f0e6ca] bg-[#fffdf8] p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-[#a98527]">Mission</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <p class="font-semibold text-slate-800">English</p>
                            <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $aboutUs->mission_en }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">Arabic</p>
                            <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $aboutUs->mission_ar }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#f0e6ca] bg-[#fffdf8] p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-[#a98527]">Vision</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <p class="font-semibold text-slate-800">English</p>
                            <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $aboutUs->vision_en }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">Arabic</p>
                            <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $aboutUs->vision_ar }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
