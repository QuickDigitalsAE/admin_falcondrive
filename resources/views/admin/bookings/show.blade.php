@extends('admin.layouts.app')

@section('title', 'Booking Details')
@section('page_title', 'Booking Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.bookings') }}" class="transition hover:text-[#9b7a28]">Bookings</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">#{{ $booking->id }}</span>
    </nav>
@endsection

@section('content')
    <div class="rounded-3xl border border-[#eee4ca] bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-[#f2ead4] bg-[#fffdf9] p-5">
                <h3 class="mb-4 text-sm font-semibold text-slate-700">Customer</h3>
                <div class="space-y-2 text-sm text-slate-700">
                    <div><span class="font-semibold">Name:</span> {{ $booking->name }}</div>
                    <div><span class="font-semibold">Number:</span> {{ $booking->number }}</div>
                    <div><span class="font-semibold">Email:</span> {{ $booking->email ?? '-' }}</div>
                    <div><span class="font-semibold">Preference:</span> {{ $booking->contact_preference ?? '-' }}</div>
                    <div><span class="font-semibold">22+ Years:</span> {{ $booking->term_22_years ? 'Yes' : 'No' }}</div>
                    <div><span class="font-semibold">6+ Months Exp:</span> {{ $booking->term_6_month_experience ? 'Yes' : 'No' }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-[#f2ead4] bg-[#fffdf9] p-5">
                <h3 class="mb-4 text-sm font-semibold text-slate-700">Rental</h3>
                <div class="space-y-2 text-sm text-slate-700">
                    <div><span class="font-semibold">Start:</span> {{ optional($booking->start_date)->format('Y-m-d') ?? '-' }} {{ $booking->start_time ?? '' }}</div>
                    <div><span class="font-semibold">End:</span> {{ optional($booking->end_date)->format('Y-m-d') ?? '-' }} {{ $booking->end_time ?? '' }}</div>
                    <div><span class="font-semibold">Type:</span> {{ $booking->rental_type ?? '-' }}</div>
                    <div><span class="font-semibold">Duration:</span> {{ $booking->rental_duration ?? '-' }}</div>
                    <div><span class="font-semibold">Rental Price:</span> {{ number_format((float) $booking->rental_price, 2) }}</div>
                    <div><span class="font-semibold">Resident/Tourist:</span> {{ $booking->resident_tourist ?? '-' }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-[#f2ead4] bg-[#fffdf9] p-5">
                <h3 class="mb-4 text-sm font-semibold text-slate-700">Add-ons</h3>
                <div class="space-y-2 text-sm text-slate-700">
                    <div><span class="font-semibold">Full Insurance:</span> {{ $booking->full_insurance ? 'Yes' : 'No' }} ({{ number_format((float) $booking->full_insurance_price, 2) }})</div>
                    <div><span class="font-semibold">Additional Driver:</span> {{ $booking->additional_driver ? 'Yes' : 'No' }} ({{ number_format((float) $booking->additional_driver_charges, 2) }})</div>
                    <div><span class="font-semibold">Baby Seat:</span> {{ $booking->baby_seat ? 'Yes' : 'No' }} ({{ number_format((float) $booking->baby_seat_price, 2) }})</div>
                    <div><span class="font-semibold">Deposit/Waiver:</span> {{ $booking->deposit_waiver ?? '-' }} ({{ number_format((float) $booking->deposit_waiver_price, 2) }})</div>
                </div>
            </div>

            <div class="rounded-2xl border border-[#f2ead4] bg-[#fffdf9] p-5">
                <h3 class="mb-4 text-sm font-semibold text-slate-700">Locations</h3>
                <div class="space-y-2 text-sm text-slate-700">
                    <div><span class="font-semibold">Delivery Location:</span> {{ $booking->delivery_location ?? '-' }}</div>
                    <div><span class="font-semibold">Delivery Price:</span> {{ number_format((float) $booking->delivery_location_price, 2) }}</div>
                    <div><span class="font-semibold">Different City Dropoff Fee:</span> {{ number_format((float) $booking->different_city_dropoff_fee, 2) }}</div>
                    <div><span class="font-semibold">Self Pickup Location:</span> {{ $booking->self_pickup_location ?? '-' }}</div>
                    <div><span class="font-semibold">Self Pickup Address:</span> {{ $booking->self_pickup_address ?? '-' }}</div>
                    <div><span class="font-semibold">Return Location:</span> {{ $booking->return_location ?? '-' }}</div>
                    <div><span class="font-semibold">Return Price:</span> {{ number_format((float) $booking->return_location_price, 2) }}</div>
                    <div><span class="font-semibold">Self Return Location:</span> {{ $booking->self_return_location ?? '-' }}</div>
                    <div><span class="font-semibold">Self Return Address:</span> {{ $booking->self_return_address ?? '-' }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-[#f2ead4] bg-[#fffdf9] p-5">
                <h3 class="mb-4 text-sm font-semibold text-slate-700">Payment</h3>
                <div class="space-y-2 text-sm text-slate-700">
                    <div><span class="font-semibold">Coupon Code:</span> {{ $booking->coupon_code ?? '-' }}</div>
                    <div><span class="font-semibold">Coupon Amount:</span> {{ number_format((float) $booking->coupon_amount, 2) }}</div>
                    <div><span class="font-semibold">Pay Now Discount:</span> {{ number_format((float) $booking->pay_now_discount, 2) }}</div>
                    <div><span class="font-semibold">Discount %:</span> {{ number_format((float) $booking->discount_percentage, 2) }}</div>
                    <div><span class="font-semibold">Subtotal:</span> {{ number_format((float) $booking->subtotal, 2) }}</div>
                    <div><span class="font-semibold">VAT %:</span> {{ number_format((float) $booking->vat_percentage, 2) }}</div>
                    <div><span class="font-semibold">VAT Amount:</span> {{ number_format((float) $booking->vat_amount, 2) }}</div>
                    <div><span class="font-semibold">Total:</span> {{ number_format((float) $booking->total_amount, 2) }}</div>
                    <div><span class="font-semibold">Flow:</span> {{ $booking->payment_flow }}</div>
                    <div><span class="font-semibold">Pay Now 20%:</span> {{ number_format((float) $booking->{'pay_now_20%_to_Reserve'}, 2) }}</div>
                    <div><span class="font-semibold">Pay At Pickup 80%:</span> {{ number_format((float) $booking->{'pay_at_pickup_80%'}, 2) }}</div>
                    <div><span class="font-semibold">Paid ID:</span> {{ $booking->paid_id ?? '-' }}</div>
                    <div><span class="font-semibold">Paid Date:</span> {{ optional($booking->paid_date)->format('Y-m-d H:i:s') ?? '-' }}</div>
                    <div><span class="font-semibold">Paid Status:</span> {{ $booking->paid_status ?? '-' }}</div>
                    <div><span class="font-semibold">Paid Via:</span> {{ $booking->paid_via ?? '-' }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-[#f2ead4] bg-[#fffdf9] p-5">
                <h3 class="mb-4 text-sm font-semibold text-slate-700">System</h3>
                <div class="space-y-2 text-sm text-slate-700">
                    <div><span class="font-semibold">Send Booking ID:</span> {{ $booking->send_booking_id ?? '-' }}</div>
                    <div><span class="font-semibold">Created At:</span> {{ optional($booking->created_at)->format('Y-m-d H:i:s') ?? '-' }}</div>
                    <div><span class="font-semibold">Updated At:</span> {{ optional($booking->updated_at)->format('Y-m-d H:i:s') ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6">
            <div class="rounded-2xl border border-[#f2ead4] bg-[#fffdf9] p-5">
                <h3 class="mb-4 text-sm font-semibold text-slate-700">Notes</h3>
                <div class="whitespace-pre-wrap text-sm text-slate-700">{{ $booking->notes ?: '-' }}</div>
            </div>

            <div class="rounded-2xl border border-[#f2ead4] bg-[#fffdf9] p-5">
                <h3 class="mb-4 text-sm font-semibold text-slate-700">Speed Response</h3>
                <div class="whitespace-pre-wrap break-words text-sm text-slate-700">{{ $booking->speed_response ?: '-' }}</div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            @can('Booking_Edit')
                <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]">
                    <i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>Edit
                </a>
            @endcan
            <a href="{{ route('admin.bookings') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
                <i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back
            </a>
        </div>
    </div>
@endsection
