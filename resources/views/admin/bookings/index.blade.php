@extends('admin.layouts.app')

@section('title', 'Bookings')
@section('page_title', 'Bookings')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Bookings</span>
    </nav>
@endsection

@section('content')
    <div class="flex h-full flex-col gap-5">
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-[1fr_auto]">
            <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
                <form method="GET" action="{{ route('admin.bookings') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#b49543]">
                                <i class="fa-solid fa-magnifying-glass text-sm"></i>
                            </span>
                            <input
                                type="text"
                                name="search"
                                value="{{ $search ?? '' }}"
                                placeholder="Search by name, number, email, coupon or transaction id"
                                class="w-full rounded-xl border border-[#eadfbe] bg-[#fffdf8] py-2.5 pl-11 pr-4 text-sm outline-none transition focus:border-[#d8bf79] focus:bg-white"
                            >
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2 sm:self-stretch">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-[#d6ab3d] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#c59626] sm:min-w-[110px]"
                        >
                            <i class="fa-solid fa-magnifying-glass mr-2 text-[12px]"></i>
                            Search
                        </button>

                        <a
                            href="{{ route('admin.bookings') }}"
                            class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]"
                            title="Reset"
                        >
                            <i class="fa-solid fa-rotate-right text-[13px]"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
                <div class="flex flex-wrap items-center justify-start gap-2 xl:justify-end">
                    @can('Booking_Add')
                        <a
                            href="{{ route('admin.bookings.create') }}"
                            class="inline-flex items-center rounded-xl bg-[#c79a2b] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#b4871d]"
                        >
                            <i class="fa-solid fa-plus mr-2 text-[13px]"></i>
                            Add Booking
                        </a>
                    @endcan

                    @can('Booking_ViewAll')
                        <a
                            href="{{ route('admin.bookings', ['is_export' => 1, 'search' => $search ?? null]) }}"
                            class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] shadow-sm transition hover:bg-[#fff8e7]"
                            title="Export CSV"
                        >
                            <i class="fa-solid fa-file-csv text-[14px]"></i>
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
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Rental</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Payment</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f6f0df] bg-white">
                        @forelse ($records as $booking)
                            <tr class="hover:bg-[#fffaf0]">
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @can('Booking_ViewAll|Booking_View')
                                            <a
                                                href="{{ route('admin.bookings.show', $booking->id) }}"
                                                class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-3 py-2 text-xs font-semibold text-[#7d6220] transition hover:bg-[#fff8e8]"
                                            >
                                                <i class="fa-solid fa-eye mr-2 text-[12px]"></i>
                                                View
                                            </a>
                                        @endcan
                                        @can('Booking_Edit')
                                            <a
                                                href="{{ route('admin.bookings.edit', $booking->id) }}"
                                                class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-3 py-2 text-xs font-semibold text-[#7d6220] transition hover:bg-[#fff8e8]"
                                            >
                                                <i class="fa-solid fa-pen-to-square mr-2 text-[12px]"></i>
                                                Edit
                                            </a>
                                        @endcan
                                        @can('Booking_Delete')
                                            <form method="POST" action="{{ route('admin.bookings.delete', $booking->id) }}" onsubmit="return confirm('Delete this booking?');">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                                >
                                                    <i class="fa-solid fa-trash mr-2 text-[12px]"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-sm font-semibold text-slate-800">{{ $booking->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $booking->number }}</div>
                                        <div class="text-xs text-slate-500">{{ $booking->email ?? '-' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-xs text-slate-700">
                                            {{ optional($booking->start_date)->format('Y-m-d') ?? '-' }}
                                            @if(!empty($booking->start_time)) {{ $booking->start_time }} @endif
                                        </div>
                                        <div class="text-xs text-slate-700">
                                            {{ optional($booking->end_date)->format('Y-m-d') ?? '-' }}
                                            @if(!empty($booking->end_time)) {{ $booking->end_time }} @endif
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $booking->rental_type ?? '-' }} / {{ $booking->resident_tourist ?? '-' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-xs text-slate-700">Flow: {{ $booking->payment_flow }}</div>
                                        <div class="text-xs text-slate-500">Status: {{ $booking->paid_status ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">Via: {{ $booking->paid_via ?? '-' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ optional($booking->created_at)->format('d M Y, h:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">No bookings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="shrink-0 border-t border-[#f2ead4] bg-[#fffdf9] px-6 py-4">
                {{ $records->links() }}
            </div>
        </div>
    </div>
@endsection

