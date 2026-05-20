@extends('admin.layouts.app')

@section('title', 'Promo Code Details')
@section('page_title', 'Promo Code Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.promo-codes') }}" class="transition hover:text-[#9b7a28]">Promo Codes</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Details</span>
    </nav>
@endsection

@section('content')
<div class="space-y-5">
    <div class="rounded-[28px] border border-[#eee4ca] bg-gradient-to-br from-[#fffaf0] to-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <span class="inline-flex rounded-full bg-[#fff4d8] px-4 py-2 text-sm font-bold tracking-[0.16em] text-[#8a6a1c]">{{ $promoCode->code }}</span>
                <h2 class="mt-4 text-2xl font-bold text-slate-800">{{ $promoCode->title ?: 'Promo Code' }}</h2>
                <p class="mt-1 text-sm text-slate-500">Created {{ optional($promoCode->created_at)->format('d M Y, h:i A') }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.promo-codes.edit', $promoCode->id) }}" class="inline-flex items-center rounded-2xl bg-[#d6ab3d] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#c59626]"><i class="fa-solid fa-pen mr-2 text-[13px]"></i>Edit</a>
                <a href="{{ route('admin.promo-codes') }}" class="inline-flex items-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        @foreach ([
            'Discount Type' => ucfirst($promoCode->discount_type),
            'Discount Value' => $promoCode->discount_type === 'percentage' ? $promoCode->discount_value . '%' : 'AED ' . number_format((float) $promoCode->discount_value, 2),
            'Minimum Amount' => 'AED ' . number_format((float) $promoCode->minimum_amount, 2),
            'Start Date' => optional($promoCode->start_date)->format('Y-m-d') ?: '-',
            'Expiry Date' => optional($promoCode->expiry_date)->format('Y-m-d') ?: '-',
            'Status' => $promoCode->status ? 'Active' : 'Inactive',
            'Deleted At' => optional($promoCode->deleted_at)->format('d M Y, h:i A') ?: '-',
            'Updated At' => optional($promoCode->updated_at)->format('d M Y, h:i A') ?: '-',
        ] as $label => $value)
            <div class="rounded-2xl border border-[#eee4ca] bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#9b7a28]">{{ $label }}</p>
                <p class="mt-2 break-words text-sm font-semibold text-slate-800">{{ $value }}</p>
            </div>
        @endforeach
    </div>
</div>
@endsection
