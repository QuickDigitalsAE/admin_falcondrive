@extends('admin.layouts.app')

@section('title', 'Customers')
@section('page_title', 'Customers')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Customers</span>
    </nav>
@endsection

@section('content')
    <div class="flex h-full flex-col gap-5">
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-[1fr_auto]">
            <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
                <form method="GET" action="{{ route('admin.customers') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#b49543]">
                                <i class="fa-solid fa-magnifying-glass text-sm"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search by customer id, name, username, email or mobile"
                                class="w-full rounded-xl border border-[#eadfbe] bg-[#fffdf8] py-2.5 pl-11 pr-4 text-sm outline-none transition focus:border-[#d8bf79] focus:bg-white">
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2 sm:self-stretch">
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-[#d6ab3d] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#c59626] sm:min-w-[110px]">
                            <i class="fa-solid fa-magnifying-glass mr-2 text-[12px]"></i>
                            Search
                        </button>

                        <a href="{{ route('admin.customers') }}"
                            class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]"
                            title="Reset">
                            <i class="fa-solid fa-rotate-right text-[13px]"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm hidden">
                <div class="flex flex-wrap items-center justify-start gap-2 xl:justify-end">
                    @can('Customer_Add')
                        <a href="{{ route('admin.customers.create') }}"
                            class="inline-flex items-center rounded-xl bg-[#c79a2b] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#b4871d]">
                            <i class="fa-solid fa-plus mr-2 text-[13px]"></i>
                            Add Customer
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-[#eee4ca] bg-white shadow-sm">
            <div class="theme-table-scroll min-h-0 flex-1 overflow-auto">
                <table class="min-w-full divide-y divide-[#f2ead4]">
                    <thead class="sticky top-0 z-10 bg-[#fffaf0]">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Actions</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Customer</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Address</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f6f0df] bg-white">
                        @forelse($customers as $customer)
                            <tr class="hover:bg-[#fffdf9]">
                                <td class="px-6 py-4 align-top">
                                    <div class="flex flex-wrap gap-2">
                                        @if(auth()->user()->can('Customer_ViewAll') || auth()->user()->can('Customer_View'))
                                            <a href="{{ route('admin.customers.show', $customer->id) }}"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]"
                                                title="View">
                                                <i class="fa-solid fa-eye text-[13px]"></i>
                                            </a>
                                        @endif

                                        @can('Customer_Edit')
                                            <!-- <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#d9c68f] bg-[#fff5d8] text-[#9b7a28] transition hover:bg-[#ffefc1]"
                                                title="Edit">
                                                <i class="fa-solid fa-pen text-[13px]"></i>
                                            </a> -->
                                        @endcan
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-800">
                                            {{ trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')) ?: 'Unnamed Customer' }}
                                        </p>
                                        <p class="mt-1 text-sm text-slate-500">
                                            <span class="font-medium text-[#9b7a28]">ID:</span> {{ $customer->customer_id }}
                                            <span class="mx-1 text-slate-300">|</span>
                                            <span class="font-medium text-[#9b7a28]">Username:</span> {{ $customer->username }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <p class="text-sm text-slate-800">{{ $customer->email }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $customer->mobile_no }}</p>
                                    <p class="mt-1 text-xs text-slate-400">DOB: {{ optional($customer->date_of_birth)->format('d M Y') ?: 'N/A' }}</p>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <p class="text-sm text-slate-800">{{ $customer->street ?: '-' }}</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ collect([$customer->city, $customer->state, $customer->country, $customer->postal_code])->filter()->implode(', ') ?: '-' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        Nationality: {{ $customer->nationality ?: '-' }} | Gender: {{ $customer->gender == 1 ? 'Male' : ($customer->gender == 2 ? 'Female' : 'N/A') }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 align-top text-sm text-slate-500">
                                    {{ $customer->created_at ? $customer->created_at->format('d M Y, h:i A') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    No customers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="shrink-0 border-t border-[#f2ead4] bg-[#fffdf9] px-6 py-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="text-sm text-slate-500">
                        Showing {{ $customers->firstItem() ?: 0 }} to {{ $customers->lastItem() ?: 0 }} of {{ $customers->total() }} results
                    </div>
                    <div>
                        {{ $customers->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
