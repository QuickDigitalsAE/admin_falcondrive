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
            <div class="overflow-hidden rounded-[30px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="bg-[radial-gradient(circle_at_top_left,_rgba(255,247,230,0.95),_rgba(255,253,248,0.98)_38%,_rgba(255,255,255,1)_100%)] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Inquiries Management</p>
                            <h1 class="mt-2 text-[30px] font-bold leading-tight text-slate-900">Inquiry Details</h1>
                            <p class="mt-2 max-w-2xl text-sm text-slate-500">Complete customer inquiry overview with contact details, rental period, vehicle interest, and submitted notes.</p>
                        </div>

                        <a
                            href="{{ route('admin.inquiries') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"
                        >
                            <i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>
                            Back to Inquiries
                        </a>
                    </div>
                </div>

                <div class="space-y-6 p-4 sm:p-6">
                    <div class="overflow-hidden rounded-[28px] bg-gradient-to-r from-[#f8e8b2] via-[#d6ab3d] to-[#9b7a28] p-[1px] shadow-[0_18px_45px_rgba(155,122,40,0.18)]">
                        <div class="flex flex-col gap-4 rounded-[27px] bg-gradient-to-r from-[#f9e7b4] via-[#d8b04a] to-[#9b7a28] px-5 py-5 text-[#fffdf8] sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full border border-white/40 bg-white/15 text-2xl font-semibold uppercase shadow-inner">
                                    {{ strtoupper(substr($inquiry->name ?: 'I', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-[28px] font-bold leading-tight">{{ $inquiry->name ?: 'Unknown Customer' }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-white/90">
                                        <span class="rounded-full bg-white/12 px-3 py-1">Inquiry Received</span>
                                        @if($inquiry->number)
                                            <span class="rounded-full bg-white/12 px-3 py-1">{{ $inquiry->number }}</span>
                                        @endif
                                        @if($inquiry->email)
                                            <span class="rounded-full bg-white/12 px-3 py-1">{{ $inquiry->email }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3 lg:justify-end">
                                <div class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold text-white">
                                    {{ optional($inquiry->created_at)->format('d M Y') ?: 'N/A' }}
                                </div>
                                <div class="rounded-full bg-[#fff7df] px-4 py-2 text-sm font-semibold text-[#8a6a1c]">
                                    {{ $inquiry->deleted_at ? 'Archived Inquiry' : 'New Inquiry' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,0.9fr)]">
                        <div class="space-y-6">
                            <div class="rounded-[28px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <div class="mb-5 flex items-center gap-3">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f5efe2] text-[#7d6220]">
                                        <i class="fa-solid fa-address-card text-lg"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-[26px] font-semibold text-slate-900">Contact Information</h3>
                                        <p class="mt-1 text-sm text-slate-500">Basic customer contact details for quick review.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="rounded-[22px] border border-[#f0e6ca] bg-[#fcfaf4] p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-[#9d8750]">Full Name</p>
                                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $inquiry->name ?: 'N/A' }}</p>
                                    </div>
                                    <div class="rounded-[22px] border border-[#f0e6ca] bg-[#fcfaf4] p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-[#9d8750]">Email Address</p>
                                        <p class="mt-2 break-all text-lg font-semibold text-slate-900">{{ $inquiry->email ?: 'N/A' }}</p>
                                    </div>
                                    <div class="rounded-[22px] border border-[#f0e6ca] bg-[#fcfaf4] p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-[#9d8750]">Phone Number</p>
                                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $inquiry->number ?: 'N/A' }}</p>
                                    </div>
                                    <div class="rounded-[22px] border border-[#f0e6ca] bg-[#fcfaf4] p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-[#9d8750]">Inquiry Time</p>
                                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ optional($inquiry->created_at)->format('h:i A') ?: 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-[28px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <div class="mb-5 flex items-center gap-3">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f5efe2] text-[#7d6220]">
                                        <i class="fa-solid fa-calendar-days text-lg"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-[26px] font-semibold text-slate-900">Rental Period</h3>
                                        <p class="mt-1 text-sm text-slate-500">Requested booking window selected by the customer.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="rounded-[22px] border border-[#e9dfc1] bg-[#fcfaf4] p-5 text-center shadow-sm">
                                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                            <i class="fa-solid fa-play text-lg"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-700">Start Date</p>
                                        <p class="mt-3 text-[30px] font-bold leading-tight text-slate-900">{{ $inquiry->from_date ? $inquiry->from_date->format('d M Y') : 'N/A' }}</p>
                                        <p class="mt-2 text-sm text-slate-500">{{ $inquiry->from_date ? $inquiry->from_date->format('l') : 'No date selected' }}</p>
                                    </div>
                                    <div class="rounded-[22px] border border-[#e9dfc1] bg-[#fcfaf4] p-5 text-center shadow-sm">
                                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                            <i class="fa-solid fa-flag-checkered text-lg"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-700">End Date</p>
                                        <p class="mt-3 text-[30px] font-bold leading-tight text-slate-900">{{ $inquiry->to_date ? $inquiry->to_date->format('d M Y') : 'N/A' }}</p>
                                        <p class="mt-2 text-sm text-slate-500">{{ $inquiry->to_date ? $inquiry->to_date->format('l') : 'No date selected' }}</p>
                                    </div>
                                </div>

                                <div class="mt-5 rounded-2xl bg-gradient-to-r from-[#d6ab3d] to-[#9b7a28] px-5 py-4 text-center text-lg font-semibold text-white shadow-sm">
                                    Rental Duration:
                                    @if($inquiry->from_date && $inquiry->to_date)
                                        {{ $inquiry->from_date->diffInDays($inquiry->to_date) + 1 }} day{{ $inquiry->from_date->diffInDays($inquiry->to_date) + 1 === 1 ? '' : 's' }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-[28px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <div class="mb-5 flex items-center gap-3">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f5efe2] text-[#7d6220]">
                                        <i class="fa-solid fa-comment-dots text-lg"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-[26px] font-semibold text-slate-900">Customer Message</h3>
                                        <p class="mt-1 text-sm text-slate-500">Additional note shared by the customer at submission time.</p>
                                    </div>
                                </div>

                                <div class="rounded-[22px] border border-[#e9dfc1] bg-[#fcfaf4] p-5">
                                    <p class="whitespace-pre-line text-[15px] leading-8 text-slate-700">{{ $inquiry->message ?: 'No customer message was submitted with this inquiry.' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="rounded-[28px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <div class="mb-5 flex items-center gap-3">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f5efe2] text-[#7d6220]">
                                        <i class="fa-solid fa-car-side text-lg"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-[26px] font-semibold text-slate-900">Selected Vehicle</h3>
                                        <p class="mt-1 text-sm text-slate-500">Vehicle and offer context chosen during inquiry.</p>
                                    </div>
                                </div>

                                <div class="rounded-[22px] border border-[#e9dfc1] bg-[#fcfaf4] p-6 text-center">
                                    <div class="mx-auto flex h-24 w-full max-w-[280px] items-center justify-center rounded-[22px] border border-[#e7dbc0] bg-white text-[#64748b] shadow-inner">
                                        <i class="fa-solid fa-car text-[42px]"></i>
                                    </div>
                                    <p class="mt-5 text-xl font-semibold text-slate-900">{{ $inquiry->car_name ?: 'No specific vehicle selected' }}</p>
                                    <p class="mt-2 text-sm text-slate-500">Customer has expressed interest in this vehicle selection.</p>
                                </div>
                            </div>

                            <div class="rounded-[28px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <div class="mb-5 flex items-center gap-3">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f5efe2] text-[#7d6220]">
                                        <i class="fa-solid fa-bolt text-lg"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-[26px] font-semibold text-slate-900">Quick Actions</h3>
                                        <p class="mt-1 text-sm text-slate-500">Instant contact actions based on provided details.</p>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    @if($inquiry->number)
                                        <a href="tel:{{ preg_replace('/\s+/', '', $inquiry->number) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-5 py-4 text-base font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                            <i class="fa-solid fa-phone mr-3"></i>
                                            Call Customer
                                        </a>
                                    @endif

                                    @if($inquiry->email)
                                        <a href="mailto:{{ $inquiry->email }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-[#d6ab3d] px-5 py-4 text-base font-semibold text-white shadow-sm transition hover:bg-[#c59626]">
                                            <i class="fa-solid fa-envelope mr-3"></i>
                                            Send Email
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-[28px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <div class="mb-5 flex items-center gap-3">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f5efe2] text-[#7d6220]">
                                        <i class="fa-solid fa-circle-info text-lg"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-[26px] font-semibold text-slate-900">Inquiry Summary</h3>
                                        <p class="mt-1 text-sm text-slate-500">Administrative reference and record status.</p>
                                    </div>
                                </div>

                                <div class="divide-y divide-[#f1e7cf] rounded-[22px] border border-[#f0e6ca] bg-[#fcfaf4]">
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="text-sm text-slate-500">Inquiry ID</span>
                                        <span class="text-lg font-semibold text-slate-900">#{{ $inquiry->id }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="text-sm text-slate-500">Submitted</span>
                                        <span class="text-base font-semibold text-slate-900">{{ optional($inquiry->created_at)->diffForHumans() ?: 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="text-sm text-slate-500">Promo Code</span>
                                        <span class="text-base font-semibold text-slate-900">{{ $inquiry->promo_code ?: 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                                        <span class="text-sm text-slate-500">Status</span>
                                        <span class="rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-800">{{ $inquiry->deleted_at ? 'Archived Inquiry' : 'New Inquiry' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('admin.layouts.partials.super-admin-audit-card', ['record' => $inquiry])
@endsection
