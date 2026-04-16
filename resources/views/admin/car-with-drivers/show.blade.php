@extends('admin.layouts.app')

@section('title', 'Car With Driver Details')
@section('page_title', 'Car With Driver Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.car-with-drivers') }}" class="transition hover:text-[#9b7a28]">Car With Driver</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">View Record</span>
    </nav>
@endsection

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Car With Driver Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">{{ $record->display_en }}</h1>
                            <p class="mt-1 text-sm text-slate-500">{{ $record->header_en }} · {{ $record->slug }}</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @if (is_null($record->deleted_at) && auth()->user()->can('CarWithDriver_Edit'))
                                <a href="{{ route('admin.car-with-drivers.edit', $record->id) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>Edit Record</a>
                            @endif
                            <a href="{{ route('admin.car-with-drivers') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 p-4 sm:p-6">
                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[0.8fr_1.2fr]">
                        <div class="space-y-5">
                            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-[#fffdf8] shadow-sm">
                                <img src="{{ $record->card_image_url ?: 'https://placehold.co/800x620/f8e8b2/5e450a?text=CWD' }}" alt="{{ $record->display_en }}" class="h-[320px] w-full object-cover">
                            </div>

                            <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <h3 class="text-sm font-semibold text-slate-900">Linked Cars</h3>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @forelse ($record->carsRelation as $car)
                                        <span class="inline-flex rounded-full border border-[#ead39a] bg-[#fff4d6] px-3 py-1 text-xs font-semibold text-[#7d6220]">{{ $car->name_en }}{{ $car->model ? ' (' . $car->model . ')' : '' }}</span>
                                    @empty
                                        <span class="text-sm text-slate-400">No cars linked.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-5">
                                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                    <div class="rounded-2xl border border-[#eadfbe] bg-white p-4"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Display EN</p><p class="mt-1 font-semibold text-slate-800">{{ $record->display_en }}</p></div>
                                    <div class="rounded-2xl border border-[#eadfbe] bg-white p-4"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Display AR</p><p class="mt-1 font-semibold text-slate-800">{{ $record->display_ar }}</p></div>
                                    <div class="rounded-2xl border border-[#eadfbe] bg-white p-4"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Header EN</p><p class="mt-1 font-semibold text-slate-800">{{ $record->header_en }}</p></div>
                                    <div class="rounded-2xl border border-[#eadfbe] bg-white p-4"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Header AR</p><p class="mt-1 font-semibold text-slate-800">{{ $record->header_ar }}</p></div>
                                    <div class="rounded-2xl border border-[#eadfbe] bg-white p-4 sm:col-span-2"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Slug</p><p class="mt-1 font-semibold text-slate-800">{{ $record->slug }}</p></div>
                                </div>
                            </div>

                            <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <h3 class="text-sm font-semibold text-slate-900">Card Content</h3>
                                <div class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Card Header EN</p><p class="mt-2 text-sm text-slate-900">{{ $record->card_header_en ?: 'N/A' }}</p></div>
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Card Header AR</p><p class="mt-2 text-sm text-slate-900">{{ $record->card_header_ar ?: 'N/A' }}</p></div>
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Card Text EN</p><p class="mt-2 whitespace-pre-line text-sm text-slate-900">{{ $record->card_text_en ?: 'N/A' }}</p></div>
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Card Text AR</p><p class="mt-2 whitespace-pre-line text-sm text-slate-900">{{ $record->card_text_ar ?: 'N/A' }}</p></div>
                                </div>
                            </div>

                            <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <h3 class="text-sm font-semibold text-slate-900">SEO</h3>
                                <div class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Meta Title EN</p><p class="mt-2 text-sm text-slate-900">{{ $record->meta_title_en }}</p></div>
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Meta Title AR</p><p class="mt-2 text-sm text-slate-900">{{ $record->meta_title_ar }}</p></div>
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Meta Description EN</p><p class="mt-2 whitespace-pre-line text-sm text-slate-900">{{ $record->meta_description_en }}</p></div>
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Meta Description AR</p><p class="mt-2 whitespace-pre-line text-sm text-slate-900">{{ $record->meta_description_ar }}</p></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                        <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-semibold text-slate-900">Content EN</h3>
                            <div class="prose prose-sm mt-4 max-w-none text-slate-600">{!! $record->content_en !!}</div>
                        </div>
                        <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-semibold text-slate-900">Content AR</h3>
                            <div class="prose prose-sm mt-4 max-w-none text-slate-600" dir="rtl">{!! $record->content_ar !!}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
