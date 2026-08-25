@csrf

@php
    $promotionImageUrl = $promotion?->image_url ?: 'https://placehold.co/200x200/f8e8b2/5e450a?text=Promotion';
    $promotionImageName = !empty($promotion?->image) ? basename($promotion?->image) : 'No file selected';
@endphp

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    @foreach ([
        ['id' => 'name_en', 'label' => 'Name EN', 'value' => old('name_en', $promotion?->name_en ?? '')],
        ['id' => 'name_ar', 'label' => 'Name AR', 'value' => old('name_ar', $promotion?->name_ar ?? '')],
        ['id' => 'slug', 'label' => 'Slug', 'value' => old('slug', $promotion?->slug ?? '')],
        ['id' => 'seo_title_en', 'label' => 'SEO Title EN', 'value' => old('seo_title_en', $promotion?->seo_title_en ?? '')],
        ['id' => 'seo_title_ar', 'label' => 'SEO Title AR', 'value' => old('seo_title_ar', $promotion?->seo_title_ar ?? '')],
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
                <select id="top_offer" name="top_offer" class="peer w-full appearance-none rounded-[18px] border {{ $errors->has('top_offer') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 pr-11 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <option value="0" {{ (string) old('top_offer', (int) ($promotion?->top_offer ?? 0)) === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ (string) old('top_offer', (int) ($promotion?->top_offer ?? 0)) === '1' ? 'selected' : '' }}>Yes</option>
                </select>
                <label for="top_offer" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">Top Offer</label>
                <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
            </div>
            @error('top_offer')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <textarea id="seo_brief_en" name="seo_brief_en" rows="4" placeholder="SEO Brief EN" class="peer w-full rounded-[18px] border {{ $errors->has('seo_brief_en') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ old('seo_brief_en', $promotion?->seo_brief_en ?? '') }}</textarea>
                <label for="seo_brief_en" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">SEO Brief EN</label>
            </div>
            @error('seo_brief_en')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <textarea id="seo_brief_ar" name="seo_brief_ar" rows="4" placeholder="SEO Brief AR" class="peer w-full rounded-[18px] border {{ $errors->has('seo_brief_ar') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ old('seo_brief_ar', $promotion?->seo_brief_ar ?? '') }}</textarea>
                <label for="seo_brief_ar" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">SEO Brief AR</label>
            </div>
            @error('seo_brief_ar')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="xl:col-span-2 min-w-0">
        <div class="space-y-2">
            <label for="description_en" class="block px-1 text-xs font-medium {{ $errors->has('description_en') ? 'text-red-500' : 'text-slate-500' }}">Description EN</label>
            <div class="resource-ckeditor-shell {{ $errors->has('description_en') ? 'is-invalid' : '' }}"><textarea id="description_en" name="description_en" rows="6">{{ old('description_en', $promotion?->description_en ?? '') }}</textarea></div>
            @error('description_en')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="xl:col-span-2 min-w-0">
        <div class="space-y-2">
            <label for="description_ar" class="block px-1 text-xs font-medium {{ $errors->has('description_ar') ? 'text-red-500' : 'text-slate-500' }}">Description AR</label>
            <div class="resource-ckeditor-shell {{ $errors->has('description_ar') ? 'is-invalid' : '' }}"><textarea id="description_ar" name="description_ar" rows="6">{{ old('description_ar', $promotion?->description_ar ?? '') }}</textarea></div>
            @error('description_ar')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-4 sm:p-5">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
        <div class="flex shrink-0 items-center gap-4">
            <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-[#d8c79d] bg-white shadow-sm">
                <img id="promotionPreview" src="{{ $promotionImageUrl }}" alt="Promotion Preview" class="h-full w-full object-cover">
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Promotion Image</h3>
                <p class="mt-1 text-sm text-slate-500">Upload a featured image for the promotion.</p>
            </div>
        </div>
        <div class="flex-1">
            <label for="image" class="group flex cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed {{ $errors->has('image') ? 'border-red-300 bg-red-50/40' : 'border-[#d8c79d] bg-white hover:border-[#c79a2b] hover:bg-[#fff8e8]' }} px-5 py-8 text-center transition">
                <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-cloud-arrow-up text-lg"></i></div>
                <p class="text-sm font-semibold text-slate-800">Click to upload promotion image</p>
                <p class="mt-1 text-xs text-slate-500">PNG, JPG, JPEG, WEBP up to 4MB</p>
                <input id="image" type="file" name="image" accept=".png,.jpg,.jpeg,.webp" class="hidden">
            </label>
            <div class="mt-3"><span id="fileName" class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 shadow-sm ring-1 ring-[#eadfbe]">{{ $promotionImageName }}</span></div>
            @error('image')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>{{ $submitLabel }}</button>
    @if (!empty($promotion?->id))
        <a href="{{ route('admin.promotions.show', $promotion?->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Promotion</a>
    @endif
    <a href="{{ route('admin.promotions') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a>
</div>
