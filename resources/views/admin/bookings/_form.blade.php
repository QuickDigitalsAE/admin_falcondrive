@csrf

@php
    $textFields = [
        ['id' => 'name', 'label' => 'Name', 'type' => 'text', 'value' => old('name', $booking->name ?? '')],
        ['id' => 'number', 'label' => 'Number', 'type' => 'text', 'value' => old('number', $booking->number ?? '')],
        ['id' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => old('email', $booking->email ?? '')],
        ['id' => 'start_date', 'label' => 'Start Date', 'type' => 'date', 'value' => old('start_date', !empty($booking?->start_date) ? optional($booking->start_date)->format('Y-m-d') : '')],
        ['id' => 'end_date', 'label' => 'End Date', 'type' => 'date', 'value' => old('end_date', !empty($booking?->end_date) ? optional($booking->end_date)->format('Y-m-d') : '')],
        ['id' => 'start_time', 'label' => 'Start Time', 'type' => 'text', 'value' => old('start_time', $booking->start_time ?? '')],
        ['id' => 'end_time', 'label' => 'End Time', 'type' => 'text', 'value' => old('end_time', $booking->end_time ?? '')],
        ['id' => 'rental_price', 'label' => 'Rental Price', 'type' => 'number', 'step' => '0.01', 'value' => old('rental_price', $booking->rental_price ?? 0)],
        ['id' => 'rental_duration', 'label' => 'Rental Duration', 'type' => 'text', 'value' => old('rental_duration', $booking->rental_duration ?? '')],
        ['id' => 'full_insurance_price', 'label' => 'Full Insurance Price', 'type' => 'number', 'step' => '0.01', 'value' => old('full_insurance_price', $booking->full_insurance_price ?? 0)],
        ['id' => 'additional_driver_charges', 'label' => 'Additional Driver Charges', 'type' => 'number', 'step' => '0.01', 'value' => old('additional_driver_charges', $booking->additional_driver_charges ?? 0)],
        ['id' => 'baby_seat_price', 'label' => 'Baby Seat Price', 'type' => 'number', 'step' => '0.01', 'value' => old('baby_seat_price', $booking->baby_seat_price ?? 0)],
        ['id' => 'deposit_waiver_price', 'label' => 'Deposit Waiver Price', 'type' => 'number', 'step' => '0.01', 'value' => old('deposit_waiver_price', $booking->deposit_waiver_price ?? 0)],
        ['id' => 'delivery_location_price', 'label' => 'Delivery Location Price', 'type' => 'number', 'step' => '0.01', 'value' => old('delivery_location_price', $booking->delivery_location_price ?? 0)],
        ['id' => 'different_city_dropoff_fee', 'label' => 'Different City Dropoff Fee', 'type' => 'number', 'step' => '0.01', 'value' => old('different_city_dropoff_fee', $booking->different_city_dropoff_fee ?? 0)],
        ['id' => 'self_pickup_address', 'label' => 'Self Pickup Address', 'type' => 'text', 'value' => old('self_pickup_address', $booking->self_pickup_address ?? '')],
        ['id' => 'return_location_price', 'label' => 'Return Location Price', 'type' => 'number', 'step' => '0.01', 'value' => old('return_location_price', $booking->return_location_price ?? 0)],
        ['id' => 'self_return_address', 'label' => 'Self Return Address', 'type' => 'text', 'value' => old('self_return_address', $booking->self_return_address ?? '')],
        ['id' => 'coupon_code', 'label' => 'Coupon Code', 'type' => 'text', 'value' => old('coupon_code', $booking->coupon_code ?? '')],
        ['id' => 'coupon_amount', 'label' => 'Coupon Amount', 'type' => 'number', 'step' => '0.01', 'value' => old('coupon_amount', $booking->coupon_amount ?? 0)],
        ['id' => 'pay_now_discount', 'label' => 'Pay Now Discount', 'type' => 'number', 'step' => '0.01', 'value' => old('pay_now_discount', $booking->pay_now_discount ?? 0)],
        ['id' => 'discount_percentage', 'label' => 'Discount Percentage', 'type' => 'number', 'step' => '0.01', 'value' => old('discount_percentage', $booking->discount_percentage ?? 0)],
        ['id' => 'subtotal', 'label' => 'Subtotal', 'type' => 'number', 'step' => '0.01', 'value' => old('subtotal', $booking->subtotal ?? 0)],
        ['id' => 'vat_percentage', 'label' => 'VAT Percentage', 'type' => 'number', 'step' => '0.01', 'value' => old('vat_percentage', $booking->vat_percentage ?? 0)],
        ['id' => 'vat_amount', 'label' => 'VAT Amount', 'type' => 'number', 'step' => '0.01', 'value' => old('vat_amount', $booking->vat_amount ?? 0)],
        ['id' => 'total_amount', 'label' => 'Total Amount', 'type' => 'number', 'step' => '0.01', 'value' => old('total_amount', $booking->total_amount ?? 0)],
        ['id' => 'pay_now_20%_to_Reserve', 'label' => 'Pay Now 20% To Reserve', 'type' => 'number', 'step' => '0.01', 'value' => old('pay_now_20%_to_Reserve', $booking->{'pay_now_20%_to_Reserve'} ?? 0)],
        ['id' => 'pay_at_pickup_80%', 'label' => 'Pay At Pickup 80%', 'type' => 'number', 'step' => '0.01', 'value' => old('pay_at_pickup_80%', $booking->{'pay_at_pickup_80%'} ?? 0)],
        ['id' => 'paid_id', 'label' => 'Paid ID', 'type' => 'text', 'value' => old('paid_id', $booking->paid_id ?? '')],
        ['id' => 'paid_date', 'label' => 'Paid Date', 'type' => 'datetime-local', 'value' => old('paid_date', !empty($booking?->paid_date) ? optional($booking->paid_date)->format('Y-m-d\TH:i') : '')],
        ['id' => 'paid_status', 'label' => 'Paid Status', 'type' => 'text', 'value' => old('paid_status', $booking->paid_status ?? '')],
        ['id' => 'paid_via', 'label' => 'Paid Via', 'type' => 'text', 'value' => old('paid_via', $booking->paid_via ?? '')],
        ['id' => 'send_booking_id', 'label' => 'Send Booking ID', 'type' => 'text', 'value' => old('send_booking_id', $booking->send_booking_id ?? '')],
    ];

    $textareas = [
        ['id' => 'delivery_location', 'label' => 'Delivery Location', 'value' => old('delivery_location', $booking->delivery_location ?? '')],
        ['id' => 'delivery_custom_address', 'label' => 'Delivery Custom Address', 'value' => old('delivery_custom_address', $booking->delivery_custom_address ?? '')],
        ['id' => 'self_pickup_location', 'label' => 'Self Pickup Location', 'value' => old('self_pickup_location', $booking->self_pickup_location ?? '')],
        ['id' => 'return_location', 'label' => 'Return Location', 'value' => old('return_location', $booking->return_location ?? '')],
        ['id' => 'return_custom_address', 'label' => 'Return Custom Address', 'value' => old('return_custom_address', $booking->return_custom_address ?? '')],
        ['id' => 'self_return_location', 'label' => 'Self Return Location', 'value' => old('self_return_location', $booking->self_return_location ?? '')],
        ['id' => 'notes', 'label' => 'Notes', 'value' => old('notes', $booking->notes ?? '')],
        ['id' => 'speed_response', 'label' => 'Speed Response', 'value' => old('speed_response', $booking->speed_response ?? '')],
    ];
@endphp

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    @foreach ($textFields as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input
                        id="{{ $field['id'] }}"
                        type="{{ $field['type'] }}"
                        name="{{ $field['id'] }}"
                        value="{{ $field['value'] }}"
                        @if(!empty($field['step'])) step="{{ $field['step'] }}" @endif
                        placeholder="{{ $field['label'] }}"
                        class="peer w-full rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">{{ $field['label'] }}</label>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach

    <div class="min-w-0">
        <div class="space-y-2">
            <label class="block px-1 text-xs font-medium tracking-[0.02em] text-slate-500">Rental Type</label>
            <select name="rental_type" class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                <option value="">Select</option>
                @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('rental_type', $booking->rental_type ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('rental_type')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <label class="block px-1 text-xs font-medium tracking-[0.02em] text-slate-500">Resident/Tourist</label>
            <select name="resident_tourist" class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                <option value="">Select</option>
                @foreach (['resident' => 'Resident', 'tourist' => 'Tourist'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('resident_tourist', $booking->resident_tourist ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('resident_tourist')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <label class="block px-1 text-xs font-medium tracking-[0.02em] text-slate-500">Deposit/Waiver</label>
            <select name="deposit_waiver" class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                <option value="">Select</option>
                @foreach (['Deposit' => 'Deposit', 'Waiver' => 'Waiver'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('deposit_waiver', $booking->deposit_waiver ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('deposit_waiver')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <label class="block px-1 text-xs font-medium tracking-[0.02em] text-slate-500">Payment Flow</label>
            <select name="payment_flow" class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                @foreach (['later' => 'Later', 'now' => 'Now'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('payment_flow', $booking->payment_flow ?? 'later') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment_flow')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <label class="block px-1 text-xs font-medium tracking-[0.02em] text-slate-500">Contact Preference</label>
            <select name="contact_preference" class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                <option value="">Select</option>
                @foreach (['whatsapp' => 'WhatsApp', 'phone' => 'Phone'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('contact_preference', $booking->contact_preference ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('contact_preference')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="xl:col-span-2 min-w-0">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            @foreach ([
                ['id' => 'full_insurance', 'label' => 'Full Insurance'],
                ['id' => 'additional_driver', 'label' => 'Additional Driver'],
                ['id' => 'baby_seat', 'label' => 'Baby Seat'],
                ['id' => 'term_22_years', 'label' => 'I am 22+ years old'],
                ['id' => 'term_6_month_experience', 'label' => 'I have 6+ months driving experience'],
            ] as $toggle)
                <label class="inline-flex items-center gap-2 rounded-2xl border border-[#eadfbe] bg-white px-4 py-3 text-sm text-slate-700">
                    <input type="hidden" name="{{ $toggle['id'] }}" value="0">
                    <input type="checkbox" name="{{ $toggle['id'] }}" value="1" class="h-4 w-4 rounded border-slate-300 text-[#d6ab3d] focus:ring-[#f7e9b5]"
                        @checked(old($toggle['id'], (bool) ($booking?->{$toggle['id']} ?? false)))>
                    <span class="font-medium">{{ $toggle['label'] }}</span>
                </label>
            @endforeach
        </div>
    </div>

    @foreach ($textareas as $field)
        <div class="xl:col-span-2 min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <textarea id="{{ $field['id'] }}" name="{{ $field['id'] }}" rows="{{ $field['id'] === 'speed_response' ? 8 : 4 }}" placeholder="{{ $field['label'] }}"
                        class="peer w-full rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ $field['value'] }}</textarea>
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">{{ $field['label'] }}</label>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach
</div>

<div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>{{ $submitLabel }}</button>
    @if (!empty($booking?->id))
        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Booking</a>
    @endif
    <a href="{{ route('admin.bookings') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a>
</div>
