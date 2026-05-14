@csrf

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    @foreach ([
        ['id' => 'name', 'label' => 'Name', 'value' => old('name', $booking->name ?? '')],
        ['id' => 'number', 'label' => 'Number', 'value' => old('number', $booking->number ?? '')],
        ['id' => 'email', 'label' => 'Email', 'value' => old('email', $booking->email ?? '')],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input id="{{ $field['id'] }}" type="text" name="{{ $field['id'] }}" value="{{ $field['value'] }}" placeholder="{{ $field['label'] }}"
                        class="peer w-full rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">{{ $field['label'] }}</label>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach

    @foreach ([
        ['id' => 'start_date', 'label' => 'Start Date', 'value' => old('start_date', !empty($booking?->start_date) ? optional($booking->start_date)->format('Y-m-d') : '')],
        ['id' => 'end_date', 'label' => 'End Date', 'value' => old('end_date', !empty($booking?->end_date) ? optional($booking->end_date)->format('Y-m-d') : '')],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input id="{{ $field['id'] }}" type="date" name="{{ $field['id'] }}" value="{{ $field['value'] }}"
                        class="peer w-full rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500' }}">{{ $field['label'] }}</label>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach

    @foreach ([
        ['id' => 'start_time', 'label' => 'Start Time', 'value' => old('start_time', $booking->start_time ?? ''), 'placeholder' => 'HH:MM'],
        ['id' => 'end_time', 'label' => 'End Time', 'value' => old('end_time', $booking->end_time ?? ''), 'placeholder' => 'HH:MM'],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input id="{{ $field['id'] }}" type="text" name="{{ $field['id'] }}" value="{{ $field['value'] }}" placeholder="{{ $field['placeholder'] }}"
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

    <div class="xl:col-span-2 min-w-0">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            @foreach ([
                ['id' => 'full_insurance', 'label' => 'Full Insurance'],
                ['id' => 'additional_driver', 'label' => 'Additional Driver'],
                ['id' => 'baby_seat', 'label' => 'Baby Seat'],
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

    @foreach ([
        ['id' => 'delivery_address', 'label' => 'Delivery Address', 'value' => old('delivery_address', $booking->delivery_address ?? ''), 'cols' => 2],
        ['id' => 'pickup_address', 'label' => 'Pickup Address', 'value' => old('pickup_address', $booking->pickup_address ?? ''), 'cols' => 2],
    ] as $field)
        <div class="xl:col-span-{{ $field['cols'] }} min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <textarea id="{{ $field['id'] }}" name="{{ $field['id'] }}" rows="3" placeholder="{{ $field['label'] }}"
                        class="peer w-full rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ $field['value'] }}</textarea>
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">{{ $field['label'] }}</label>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach

    @foreach ([
        ['id' => 'delivery_area', 'label' => 'Delivery Area', 'value' => old('delivery_area', $booking->delivery_area ?? '')],
        ['id' => 'pickup_area', 'label' => 'Pickup Area', 'value' => old('pickup_area', $booking->pickup_area ?? '')],
        ['id' => 'delivery_price', 'label' => 'Delivery Price', 'value' => old('delivery_price', $booking->delivery_price ?? 0)],
        ['id' => 'pickup_price', 'label' => 'Pickup Price', 'value' => old('pickup_price', $booking->pickup_price ?? 0)],
        ['id' => 'coupon_code', 'label' => 'Coupon Code', 'value' => old('coupon_code', $booking->coupon_code ?? '')],
        ['id' => 'discount_percentage', 'label' => 'Discount %', 'value' => old('discount_percentage', $booking->discount_percentage ?? 0)],
        ['id' => 'paid_id', 'label' => 'Paid ID', 'value' => old('paid_id', $booking->paid_id ?? '')],
        ['id' => 'paid_status', 'label' => 'Paid Status', 'value' => old('paid_status', $booking->paid_status ?? '')],
        ['id' => 'paid_via', 'label' => 'Paid Via', 'value' => old('paid_via', $booking->paid_via ?? '')],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input id="{{ $field['id'] }}" type="text" name="{{ $field['id'] }}" value="{{ $field['value'] }}" placeholder="{{ $field['label'] }}"
                        class="peer w-full rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">{{ $field['label'] }}</label>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach

    <div class="xl:col-span-2 min-w-0">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach ([
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

    @foreach ([
        ['id' => 'description', 'label' => 'Description', 'value' => old('description', $booking->description ?? '')],
        ['id' => 'notes', 'label' => 'Notes', 'value' => old('notes', $booking->notes ?? '')],
    ] as $field)
        <div class="xl:col-span-2 min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <textarea id="{{ $field['id'] }}" name="{{ $field['id'] }}" rows="4" placeholder="{{ $field['label'] }}"
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
