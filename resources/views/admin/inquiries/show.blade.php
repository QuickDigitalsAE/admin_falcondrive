@extends('admin.layouts.app')

@section('title', 'Inquiry Details')
@section('page_title', 'Inquiry Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.inquiries') }}" class="transition hover:text-[#9b7a28]">Inquiries</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Inquiry Details</span>
    </nav>
@endsection

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl space-y-6">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Inquiries Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">Inquiry Details</h1>
                            <p class="mt-1 text-sm text-slate-500">Detailed view of the selected inquiry.</p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @can('Inquiry_Edit')
                                @if (!$inquiry->deleted_at)
                                    <a
                                        href="{{ route('admin.inquiries.edit', $inquiry->id) }}"
                                        class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"
                                    >
                                        <i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>
                                        Edit Inquiry
                                    </a>
                                @endif
                            @endcan

                            <a
                                href="{{ route('admin.inquiries') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"
                            >
                                <i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>
                                Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                        <div class="mb-5">
                            <h3 class="text-lg font-semibold text-slate-900">Inquiry Information</h3>
                            <p class="mt-1 text-sm text-slate-500">View contact, vehicle, and message details.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Name</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $inquiry->name ?: 'N/A' }}</p>
                            </div>

                            <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Number</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $inquiry->number ?: 'N/A' }}</p>
                            </div>

                            <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Email</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $inquiry->email ?: 'N/A' }}</p>
                            </div>

                            <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Promo Code</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $inquiry->promo_code ?: 'N/A' }}</p>
                            </div>

                            <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4 sm:col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Car Name</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $inquiry->car_name ?: 'N/A' }}</p>
                            </div>

                            <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4 sm:col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Message</p>
                                <p class="mt-2 whitespace-pre-line text-sm text-slate-900">{{ $inquiry->message ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('admin.layouts.partials.super-admin-audit-card', ['record' => $inquiry])
@endsection
