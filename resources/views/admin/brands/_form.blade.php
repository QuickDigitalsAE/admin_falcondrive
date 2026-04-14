@csrf

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="name_en" type="text" name="name_en" value="{{ old('name_en', $brand->name_en ?? '') }}" placeholder="Brand name in English"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('name_en') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="name_en" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('name_en') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">Name EN</label>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                <p class="text-xs text-slate-500">Primary brand name for frontend and admin use</p>
                <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#b4861f]">Required</span>
            </div>
            @error('name_en')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="name_ar" type="text" name="name_ar" value="{{ old('name_ar', $brand->name_ar ?? '') }}" placeholder="Brand name in Arabic"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('name_ar') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="name_ar" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('name_ar') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">Name AR</label>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                <p class="text-xs text-slate-500">Localized brand name for Arabic content</p>
                <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Optional</span>
            </div>
            @error('name_ar')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="slug" type="text" name="slug" value="{{ old('slug', $brand->slug ?? '') }}" placeholder="brand-slug"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('slug') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="slug" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('slug') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">Slug</label>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                <p class="text-xs text-slate-500">Used in URLs and SEO routing</p>
                <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#b4861f]">Required</span>
            </div>
            @error('slug')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0 xl:col-span-3">
        <div class="space-y-2">
            <div class="relative">
                <textarea id="description_en" name="description_en" rows="5" placeholder="Description in English"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('description_en') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ old('description_en', $brand->description_en ?? '') }}</textarea>
                <label for="description_en" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">Description EN</label>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 px-1"><p class="text-xs text-slate-500">Main brand introduction for English content</p><span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Optional</span></div>
        </div>
    </div>

    <div class="min-w-0 xl:col-span-3">
        <div class="space-y-2">
            <div class="relative">
                <textarea id="description_ar" name="description_ar" rows="5" placeholder="Description in Arabic"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('description_ar') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ old('description_ar', $brand->description_ar ?? '') }}</textarea>
                <label for="description_ar" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">Description AR</label>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 px-1"><p class="text-xs text-slate-500">Localized brand introduction for Arabic content</p><span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Optional</span></div>
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="seo_title_en" type="text" name="seo_title_en" value="{{ old('seo_title_en', $brand->seo_title_en ?? '') }}" placeholder="SEO title in English"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('seo_title_en') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="seo_title_en" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">SEO Title EN</label>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 px-1"><p class="text-xs text-slate-500">Search engine title for English pages</p><span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Optional</span></div>
        </div>
    </div>

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <input id="seo_title_ar" type="text" name="seo_title_ar" value="{{ old('seo_title_ar', $brand->seo_title_ar ?? '') }}" placeholder="SEO title in Arabic"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('seo_title_ar') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                <label for="seo_title_ar" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">SEO Title AR</label>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 px-1"><p class="text-xs text-slate-500">Search engine title for Arabic pages</p><span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Optional</span></div>
        </div>
    </div>

    <div class="min-w-0 xl:col-span-3 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="space-y-2">
            <div class="relative">
                <textarea id="seo_brief_en" name="seo_brief_en" rows="4" placeholder="SEO brief in English"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('seo_brief_en') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ old('seo_brief_en', $brand->seo_brief_en ?? '') }}</textarea>
                <label for="seo_brief_en" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">SEO Brief EN</label>
            </div>
        </div>
        <div class="space-y-2">
            <div class="relative">
                <textarea id="seo_brief_ar" name="seo_brief_ar" rows="4" placeholder="SEO brief in Arabic"
                    class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('seo_brief_ar') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ old('seo_brief_ar', $brand->seo_brief_ar ?? '') }}</textarea>
                <label for="seo_brief_ar" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">SEO Brief AR</label>
            </div>
        </div>
    </div>
</div>

<div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-4 sm:p-5">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
        <div class="flex shrink-0 items-center gap-4">
            <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-[#d8c79d] bg-white shadow-sm">
                <img id="logoPreview" src="{{ $brand?->logo_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($brand->name_en ?? 'Brand') . '&background=F8E8B2&color=5E450A&size=200' }}" alt="Logo Preview" class="h-full w-full object-cover">
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Brand Logo</h3>
                <p class="mt-1 text-sm text-slate-500">Upload a JPG, PNG, or WEBP image for this brand.</p>
            </div>
        </div>

        <div class="flex-1">
            <label for="logo" class="group flex cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed {{ $errors->has('logo') ? 'border-red-300 bg-red-50/40' : 'border-[#d8c79d] bg-white hover:border-[#c79a2b] hover:bg-[#fff8e8]' }} px-5 py-8 text-center transition">
                <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-cloud-arrow-up text-lg"></i></div>
                <p class="text-sm font-semibold text-slate-800">Click to upload brand logo</p>
                <p class="mt-1 text-xs text-slate-500">PNG, JPG, JPEG, WEBP up to 2MB</p>
                <input id="logo" type="file" name="logo" accept=".png,.jpg,.jpeg,.webp" class="hidden">
            </label>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <span id="fileName" class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 shadow-sm ring-1 ring-[#eadfbe]">{{ !empty($brand?->logo) ? basename($brand->logo) : 'No file selected' }}</span>
                @if(!empty($brand?->logo_url))
                    <label class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-3 py-2 text-xs font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
                        <input type="checkbox" name="remove_logo" value="1" class="mr-2">
                        Remove Current
                    </label>
                @endif
                <button type="button" id="removeImageBtn" class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-3 py-2 text-xs font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-trash-can mr-2"></i>Remove</button>
            </div>
            @error('logo')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="rounded-[22px] border border-dashed border-[#eadfbe] bg-[#fffdf8] px-4 py-4 sm:px-5">
    <div class="flex items-start gap-3">
        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-circle-info text-sm"></i></div>
        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-slate-800">Before saving</h3>
            <p class="mt-1 text-sm text-slate-500">Check bilingual names, keep the slug clean, and upload a proper brand logo if available.</p>
        </div>
    </div>
</div>

<div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>{{ $submitLabel }}</button>
    @if(!empty($brand?->id))
        <a href="{{ route('admin.brands.show', $brand->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Brand</a>
    @endif
    <a href="{{ route('admin.brands') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a>
</div>
