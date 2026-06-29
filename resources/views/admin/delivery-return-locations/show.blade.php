@extends('admin.layouts.app')

@section('title', 'Delivery & Return Location Details')
@section('page_title', 'Delivery & Return Location Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500"><a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a><i class="fas fa-chevron-right text-[10px] text-slate-400"></i><a href="{{ route('admin.delivery-return-locations') }}" class="transition hover:text-[#9b7a28]">Delivery & Return Locations</a><i class="fas fa-chevron-right text-[10px] text-slate-400"></i><span class="font-medium text-slate-700">Details</span></nav>
@endsection

@section('content')
<section class="w-full pb-8">
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
            <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Location Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">Location Details</h1>
                        <p class="mt-1 text-sm text-slate-500">Detailed view of delivery or return location.</p>
                        </div>
                    <div class="flex flex-wrap gap-3">
                        @if (is_null($location->deleted_at))
                            <a href="{{ route('admin.delivery-return-locations.edit', $location->id) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>Edit Location</a>
                        @endif
                        <a href="{{ route('admin.delivery-return-locations') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-6">
                <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                    <div class="mb-5">
                        <h3 class="text-lg font-semibold text-slate-900">Location Information</h3>
                        <p class="mt-1 text-sm text-slate-500">Title, detail, pickup mapping, coordinates, price and type details.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Title</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $location->title ?: 'N/A' }}</p></div>
                        <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4 sm:col-span-2 xl:col-span-3"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Detail</p><p class="mt-2 text-sm font-semibold text-slate-900 whitespace-pre-line">{{ $location->detail ?: 'N/A' }}</p></div>
                        <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">WebID</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $location->web_id ?: 'N/A' }}</p></div>
                        <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Longitude</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $location->longitude !== null ? $location->longitude : 'N/A' }}</p></div>
                        <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Latitude</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $location->latitude !== null ? $location->latitude : 'N/A' }}</p></div>
                        <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Price</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $location->price ?: 'N/A' }}</p></div>
                        <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Type</p><p class="mt-2"><span class="inline-flex rounded-full {{ $location->type === 'Return location' ? 'bg-blue-50 text-blue-700 ring-blue-100' : 'bg-emerald-50 text-emerald-700 ring-emerald-100' }} px-3 py-1 text-xs font-semibold ring-1">{{ $location->type ?: 'N/A' }}</span></p></div>
                        <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Created At</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ optional($location->created_at)->format('d M Y, h:i A') ?: 'N/A' }}</p></div>
                        <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Updated At</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ optional($location->updated_at)->format('d M Y, h:i A') ?: 'N/A' }}</p></div>
                        <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Deleted At</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ optional($location->deleted_at)->format('d M Y, h:i A') ?: 'N/A' }}</p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@includeWhen(View::exists('admin.layouts.partials.super-admin-audit-card'), 'admin.layouts.partials.super-admin-audit-card', ['record' => $location])
@endsection
