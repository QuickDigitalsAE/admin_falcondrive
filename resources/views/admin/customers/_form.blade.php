@php
    $isEdit = !is_null($customer);
    $value = fn (string $key, $default = null) => old($key, $customer?->{$key} ?? $default);
@endphp

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    @if($isEdit)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input type="text" value="{{ $customer->customer_id }}" readonly
                        class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fdfaf2] px-4 pt-6 pb-2 text-sm text-slate-700 outline-none min-h-[58px]">
                    <label class="pointer-events-none absolute left-4 top-2.5 bg-[#fdfaf2] px-1 text-xs font-medium text-slate-500">
                        Speed Customer ID
                    </label>
                </div>
                <p class="px-1 text-xs text-slate-500">This is the id returned by the Speed system.</p>
            </div>
        </div>
    @endif

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="username" type="text" name="username" value="{{ $value('username') }}" placeholder="Username"
                    autocomplete="off" autocapitalize="off" spellcheck="false"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('username') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="username"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('username') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">
                    Username
                </label>
            </div>

            @error('username')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="first_name" type="text" name="first_name" value="{{ $value('first_name') }}" placeholder="First Name"
                    autocomplete="off" autocapitalize="off" spellcheck="false"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('first_name') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="first_name"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('first_name') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">
                    First Name
                </label>
            </div>

            @error('first_name')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="last_name" type="text" name="last_name" value="{{ $value('last_name') }}" placeholder="Last Name"
                    autocomplete="off" autocapitalize="off" spellcheck="false"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('last_name') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="last_name"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('last_name') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">
                    Last Name
                </label>
            </div>

            @error('last_name')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="email" type="email" name="email" value="{{ $value('email') }}" placeholder="Email Address"
                    autocomplete="off" autocapitalize="off" spellcheck="false"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('email') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="email"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('email') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">
                    Email Address
                </label>
            </div>

            @error('email')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="mobile_no" type="text" name="mobile_no" value="{{ $value('mobile_no') }}" placeholder="Mobile No"
                    autocomplete="off" autocapitalize="off" spellcheck="false"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('mobile_no') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="mobile_no"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('mobile_no') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">
                    Mobile No
                </label>
            </div>

            @error('mobile_no')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="nationality" type="text" name="nationality" value="{{ $value('nationality') }}" placeholder="Nationality"
                    autocomplete="off" autocapitalize="off" spellcheck="false"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('nationality') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="nationality"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('nationality') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">
                    Nationality
                </label>
            </div>

            @error('nationality')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="date_of_birth" type="date" name="date_of_birth"
                    value="{{ old('date_of_birth', $customer?->date_of_birth ? optional($customer->date_of_birth)->format('Y-m-d') : null) }}"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('date_of_birth') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="date_of_birth"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('date_of_birth') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all">
                    Date of Birth
                </label>
            </div>

            @error('date_of_birth')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <select id="gender" name="gender"
                    class="peer w-full min-w-0 appearance-none rounded-[18px] border {{ $errors->has('gender') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 pr-11 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <option value="">Select Gender</option>
                    <option value="1" {{ (string) $value('gender') === '1' ? 'selected' : '' }}>Male</option>
                    <option value="2" {{ (string) $value('gender') === '2' ? 'selected' : '' }}>Female</option>
                </select>
                <label for="gender"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('gender') ? 'text-red-500' : 'text-slate-500' }}">
                    Gender
                </label>
                <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>

            @error('gender')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="location_id" type="number" name="location_id" value="{{ $value('location_id') }}" placeholder="Location ID"
                    min="1" step="1"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('location_id') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="location_id"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('location_id') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all">
                    Location ID
                </label>
            </div>

            @error('location_id')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0 xl:col-span-3">
        <div class="space-y-2">
            <div class="relative">
                <input id="street" type="text" name="street" value="{{ $value('street') }}" placeholder="Street Address"
                    autocomplete="off" autocapitalize="off" spellcheck="false"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('street') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="street"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('street') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all">
                    Street Address
                </label>
            </div>
            @error('street')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="city" type="text" name="city" value="{{ $value('city') }}" placeholder="City"
                    autocomplete="off" autocapitalize="off" spellcheck="false"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('city') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="city"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('city') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all">
                    City
                </label>
            </div>
            @error('city')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="state" type="text" name="state" value="{{ $value('state') }}" placeholder="State"
                    autocomplete="off" autocapitalize="off" spellcheck="false"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('state') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="state"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('state') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all">
                    State
                </label>
            </div>
            @error('state')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="country" type="text" name="country" value="{{ $value('country') }}" placeholder="Country"
                    autocomplete="off" autocapitalize="off" spellcheck="false"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('country') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="country"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('country') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all">
                    Country
                </label>
            </div>
            @error('country')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="postal_code" type="text" name="postal_code" value="{{ $value('postal_code') }}" placeholder="Postal Code"
                    autocomplete="off" autocapitalize="off" spellcheck="false"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('postal_code') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="postal_code"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('postal_code') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all">
                    Postal Code
                </label>
            </div>
            @error('postal_code')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="password" type="password" name="password" placeholder="Password"
                    autocomplete="new-password"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('password') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 pr-12 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="password"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('password') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all">
                    {{ $isEdit ? 'New Password' : 'Password' }}
                </label>
                <button type="button" class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#a27d20]" data-target="password">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                <p class="text-xs text-slate-500">
                    {{ $isEdit ? 'Leave blank if you do not want to change the local login password.' : 'Set the local login password for this customer.' }}
                </p>
                <span class="text-[10px] font-semibold uppercase tracking-[0.2em] {{ $isEdit ? 'text-slate-400' : 'text-[#b4861f]' }}">
                    {{ $isEdit ? 'Optional' : 'Required' }}
                </span>
            </div>

            @error('password')
                <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="password_confirmation" type="password" name="password_confirmation"
                    placeholder="Confirm Password" autocomplete="new-password"
                    class="peer w-full min-w-0 rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 pt-6 pb-2 pr-12 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5] min-h-[58px]">
                <label for="password_confirmation"
                    class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500 transition-all">
                    Confirm Password
                </label>
                <button type="button" class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#a27d20]" data-target="password_confirmation">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>

            <p class="px-1 text-xs text-slate-500">Re-enter the password to confirm the change.</p>
        </div>
    </div>
</div>

<div class="mt-6 rounded-[24px] border border-dashed border-[#eadfbe] bg-[#fffdf8] px-5 py-4">
    <div class="flex items-start gap-3">
        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]">
            <i class="fa-solid fa-circle-info text-sm"></i>
        </div>
        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-slate-800">Speed sync</h3>
            <p class="mt-1 text-sm text-slate-500">
                Saving this form will also create or update the customer in the Speed system before the local record is updated.
            </p>
        </div>
    </div>
</div>

