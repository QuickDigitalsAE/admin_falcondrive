@csrf

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    @foreach ([
        ['id' => 'code', 'label' => 'Promo Code', 'value' => old('code', $promoCode?->code ?? ''), 'type' => 'text'],
        ['id' => 'title', 'label' => 'Title', 'value' => old('title', $promoCode?->title ?? ''), 'type' => 'text'],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input id="{{ $field['id'] }}" type="{{ $field['type'] }}" name="{{ $field['id'] }}" value="{{ $field['value'] }}" placeholder="{{ $field['label'] }}"
                        class="peer w-full rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">{{ $field['label'] }}</label>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <select id="discount_type" name="discount_type"
                    class="peer w-full appearance-none rounded-[18px] border {{ $errors->has('discount_type') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 pr-11 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <option value="fixed" {{ old('discount_type', $promoCode?->discount_type ?? 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                    <option value="percentage" {{ old('discount_type', $promoCode?->discount_type ?? '') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                </select>
                <label for="discount_type" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('discount_type') ? 'text-red-500' : 'text-slate-500' }}">Discount Type</label>
                <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
            </div>
            @error('discount_type')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    @foreach ([
        ['id' => 'discount_value', 'label' => 'Discount Value', 'value' => old('discount_value', $promoCode?->discount_value ?? '')],
        ['id' => 'minimum_amount', 'label' => 'Minimum Amount', 'value' => old('minimum_amount', $promoCode?->minimum_amount ?? 0)],
        ['id' => 'start_date', 'label' => 'Start Date', 'value' => old('start_date', optional($promoCode?->start_date)->format('Y-m-d')), 'type' => 'date'],
        ['id' => 'expiry_date', 'label' => 'Expiry Date', 'value' => old('expiry_date', optional($promoCode?->expiry_date)->format('Y-m-d')), 'type' => 'date'],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input id="{{ $field['id'] }}" type="{{ $field['type'] ?? 'number' }}" step="0.01" name="{{ $field['id'] }}" value="{{ $field['value'] }}" placeholder="{{ $field['label'] }}"
                        class="peer w-full rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }}">{{ $field['label'] }}</label>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <select id="status" name="status"
                    class="peer w-full appearance-none rounded-[18px] border {{ $errors->has('status') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 pr-11 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <option value="1" {{ (int) old('status', $promoCode?->status ?? 1) === 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ (int) old('status', $promoCode?->status ?? 1) === 0 ? 'selected' : '' }}>Inactive</option>
                </select>
                <label for="status" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('status') ? 'text-red-500' : 'text-slate-500' }}">Status</label>
                <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
            </div>
            @error('status')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-4 sm:p-5">
    <div class="flex items-start gap-4">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#f8e8b2] text-[#a27d20]">
            <i class="fa-solid fa-ticket text-lg"></i>
        </div>
        <div>
            <h3 class="text-sm font-semibold text-slate-800">Frontend Usage</h3>
            <p class="mt-1 text-sm leading-6 text-slate-500">
                User will enter this code in the website field <strong>Have a promo code?</strong>. The API will validate the code and return the discount amount and final amount without page refresh.
            </p>
        </div>
    </div>
</div>

<div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]">
        <i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>{{ $submitLabel }}
    </button>

    @if (!empty($promoCode?->id))
        <a href="{{ route('admin.promo-codes.show', $promoCode->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
            <i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Promo Code
        </a>
    @endif

    <a href="{{ route('admin.promo-codes') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
        <i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel
    </a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const codeInput = document.getElementById('code');

    if (codeInput) {
        codeInput.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/\s+/g, '');
        });
    }
});
</script>
@endpush
