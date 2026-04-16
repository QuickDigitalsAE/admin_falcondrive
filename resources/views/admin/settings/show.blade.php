@php
    $normalizedValuePath = !empty($setting->value) ? ltrim(str_replace('\\', '/', (string) $setting->value), '/') : '';
    $assetName = $normalizedValuePath !== '' ? basename($normalizedValuePath) : basename((string) $setting->value);
@endphp

@extends('admin.layouts.app')

@section('title', 'Setting Details')
@section('page_title', 'Setting Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.settings') }}" class="transition hover:text-[#9b7a28]">Settings</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Setting Details</span>
    </nav>
@endsection

@section('content')
<section class="w-full pb-8">
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
            <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Website Settings</p>
                        <h1 class="text-[28px] font-bold leading-tight text-slate-900">{{ $setting->display_name }}</h1>
                        <p class="mt-1 font-mono text-sm text-slate-500">{{ $setting->key }}</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @can('Setting_Edit')
                            <a href="{{ route('admin.settings.edit', $setting->id) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>Edit Setting</a>
                        @endcan
                        <a href="{{ route('admin.settings') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                    </div>
                </div>
            </div>

            <div class="space-y-6 p-4 sm:p-6">
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-[0.9fr_1.1fr]">
                    <div class="space-y-5">
                        <div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-5">
                            <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                <div class="rounded-2xl border border-[#eadfbe] bg-white p-4"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Display Name</p><p class="mt-1 font-semibold text-slate-800">{{ $setting->display_name }}</p></div>
                                <div class="rounded-2xl border border-[#eadfbe] bg-white p-4"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Key</p><p class="mt-1 break-all font-mono text-xs text-slate-800">{{ $setting->key }}</p></div>
                                <div class="rounded-2xl border border-[#eadfbe] bg-white p-4"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Type</p><p class="mt-1 font-semibold text-slate-800">{{ $setting->type }}</p></div>
                                <div class="rounded-2xl border border-[#eadfbe] bg-white p-4"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Order</p><p class="mt-1 font-semibold text-slate-800">{{ $setting->order }}</p></div>
                                <div class="rounded-2xl border border-[#eadfbe] bg-white p-4 sm:col-span-2"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Group</p><p class="mt-1 font-semibold text-slate-800">{{ $setting->group ?: 'Ungrouped' }}</p></div>
                            </div>
                        </div>

                        <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-semibold text-slate-900">Audit</h3>
                            <div class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Created At</p><p class="mt-2 text-sm text-slate-900">{{ optional($setting->created_at)->format('d M Y, h:i A') ?: 'N/A' }}</p></div>
                                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Updated At</p><p class="mt-2 text-sm text-slate-900">{{ optional($setting->updated_at)->format('d M Y, h:i A') ?: 'N/A' }}</p></div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-semibold text-slate-900">Value</h3>
                            <div class="mt-4 rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                @if ($setting->type === 'image' && $setting->value_url)
                                    <div class="space-y-4">
                                        <img src="{{ $setting->value_url }}" alt="{{ $setting->display_name }}" class="max-h-72 w-full rounded-2xl border border-[#eadfbe] object-contain bg-white p-3">
                                        <div class="flex flex-wrap gap-3">
                                            <a href="{{ $setting->value_url }}" target="_blank" class="inline-flex items-center rounded-2xl bg-[#d6ab3d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#c59626]"><i class="fa-solid fa-arrow-up-right-from-square mr-2 text-[12px]"></i>Open Image</a>
                                        </div>
                                        <pre class="whitespace-pre-wrap break-words font-mono text-sm text-slate-800">{{ $setting->value }}</pre>
                                    </div>
                                @elseif ($setting->type === 'file' && $setting->value_url)
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3 rounded-2xl border border-[#eadfbe] bg-white p-4">
                                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-[#fff4d6] text-[#8b6717]"><i class="fa-solid fa-file-arrow-down"></i></span>
                                            <div class="min-w-0">
                                                <p class="truncate font-semibold text-slate-900">{{ $assetName }}</p>
                                                <p class="text-xs text-slate-500">Stored file for this setting.</p>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-3">
                                            <a href="{{ $setting->value_url }}" target="_blank" class="inline-flex items-center rounded-2xl bg-[#d6ab3d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#c59626]"><i class="fa-solid fa-download mr-2 text-[12px]"></i>Open File</a>
                                        </div>
                                        <pre class="whitespace-pre-wrap break-words font-mono text-sm text-slate-800">{{ $setting->value }}</pre>
                                    </div>
                                @else
                                    <pre class="whitespace-pre-wrap break-words font-mono text-sm text-slate-800">{{ $setting->value ?: 'No value saved.' }}</pre>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-semibold text-slate-900">Inner Details</h3>
                            @if ($setting->decoded_details)
                                <div class="mt-4 rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                    <pre class="whitespace-pre-wrap break-words font-mono text-sm text-slate-800">{{ json_encode($setting->decoded_details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            @else
                                <div class="mt-4 rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                    <pre class="whitespace-pre-wrap break-words font-mono text-sm text-slate-800">{{ $setting->details ?: 'No details saved.' }}</pre>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
