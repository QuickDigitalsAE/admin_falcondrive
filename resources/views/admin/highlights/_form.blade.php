@csrf

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    @foreach ([
        ['id' => 'title_en', 'label' => 'Title EN', 'value' => old('title_en', $highlight->title_en ?? '')],
        ['id' => 'title_ar', 'label' => 'Title AR', 'value' => old('title_ar', $highlight->title_ar ?? '')],
        ['id' => 'url', 'label' => 'URL', 'value' => old('url', $highlight->url ?? ''), 'type' => 'text'],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input id="{{ $field['id'] }}" type="{{ $field['type'] ?? 'text' }}" name="{{ $field['id'] }}" value="{{ $field['value'] }}" placeholder="{{ $field['label'] }}"
                        class="peer w-full rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">{{ $field['label'] }}</label>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="min-w-0">
        <div class="space-y-2">
            <div class="rounded-[24px] border {{ $errors->has('sorting') ? 'border-red-300' : 'border-[#eadfbe]' }} bg-gradient-to-br from-[#fffdf8] to-white p-5 shadow-sm h-full">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#b4861f]">Sort Order</p>
                        <p class="mt-1 text-sm text-slate-500">Highlight lists in admin tables and frontend APIs will follow this order.</p>
                    </div>
                    <span class="rounded-full bg-[#f8e8b2] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#7d6220]">Global</span>
                </div>

                <div class="mt-4 relative">
                    <select id="sorting" name="sorting" data-current-sorting="{{ old('sorting', $highlight?->sorting ?? '') }}"
                        class="peer w-full appearance-none rounded-[20px] border {{ $errors->has('sorting') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 pr-11 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[60px] shadow-sm">
                        <option value="">Loading sort order...</option>
                    </select>
                    <label for="sorting" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('sorting') ? 'text-red-500' : 'text-slate-500' }}">Sort Order</label>
                    <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                <p class="text-xs text-slate-500">Set the display order for highlights across the project</p>
                <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Optional</span>
            </div>
            @error('sorting')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-4 sm:p-5">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
        <div class="flex shrink-0 items-center gap-4">
            <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-[#d8c79d] bg-white shadow-sm">
                <img id="highlightPreview" src="{{ $highlight->image_url ?? 'https://placehold.co/200x200/f8e8b2/5e450a?text=Highlight' }}" alt="Highlight Preview" class="h-full w-full object-cover">
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Highlight Image</h3>
                <p class="mt-1 text-sm text-slate-500">Upload a featured image for the highlight.</p>
            </div>
        </div>

        <div class="flex-1">
            <label for="image" class="group flex cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed {{ $errors->has('image') ? 'border-red-300 bg-red-50/40' : 'border-[#d8c79d] bg-white hover:border-[#c79a2b] hover:bg-[#fff8e8]' }} px-5 py-8 text-center transition">
                <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-cloud-arrow-up text-lg"></i></div>
                <p class="text-sm font-semibold text-slate-800">Click to upload highlight image</p>
                <p class="mt-1 text-xs text-slate-500">PNG, JPG, JPEG, WEBP up to 4MB</p>
                <input id="image" type="file" name="image" accept=".png,.jpg,.jpeg,.webp" class="hidden">
            </label>

            <div class="mt-3"><span id="fileName" class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 shadow-sm ring-1 ring-[#eadfbe]">{{ !empty($highlight?->image) ? basename($highlight->image) : 'No file selected' }}</span></div>
            @error('image')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>{{ $submitLabel }}</button>
    @if (!empty($highlight?->id))
        <a href="{{ route('admin.highlights.show', $highlight->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Highlight</a>
    @endif
    <a href="{{ route('admin.highlights') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a>
</div>
