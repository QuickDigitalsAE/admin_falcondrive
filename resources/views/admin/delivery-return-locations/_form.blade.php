@csrf

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    @foreach ([
        ['id' => 'city', 'label' => 'City', 'value' => old('city', $location?->city ?? '')],
        ['id' => 'price', 'label' => 'Price', 'value' => old('price', $location?->price ?? '')],
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

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <select id="type" name="type"
                    class="peer w-full appearance-none rounded-[18px] border {{ $errors->has('type') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 pr-11 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <option value="">Select Type</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" {{ old('type', $location?->type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <label for="type" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('type') ? 'text-red-500' : 'text-slate-500' }}">Type</label>
                <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
            </div>
            @error('type')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>{{ $submitLabel }}</button>
    @if (!empty($location?->id))
        <a href="{{ route('admin.delivery-return-locations.show', $location->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Location</a>
    @endif
    <a href="{{ route('admin.delivery-return-locations') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a>
</div>
