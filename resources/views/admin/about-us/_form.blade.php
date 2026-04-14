@csrf

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="space-y-2">
        <label for="seo_title_en" class="text-sm font-semibold text-slate-700">SEO Title (EN)</label>
        <input id="seo_title_en" type="text" name="seo_title_en" value="{{ old('seo_title_en', $aboutUs->seo_title_en ?? '') }}"
            class="w-full rounded-2xl border {{ $errors->has('seo_title_en') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
        @error('seo_title_en')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="seo_title_ar" class="text-sm font-semibold text-slate-700">SEO Title (AR)</label>
        <input id="seo_title_ar" type="text" name="seo_title_ar" value="{{ old('seo_title_ar', $aboutUs->seo_title_ar ?? '') }}"
            class="w-full rounded-2xl border {{ $errors->has('seo_title_ar') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
        @error('seo_title_ar')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2 xl:col-span-2">
        <label for="seo_brief_en" class="text-sm font-semibold text-slate-700">SEO Brief (EN)</label>
        <textarea id="seo_brief_en" name="seo_brief_en" rows="3"
            class="w-full rounded-2xl border {{ $errors->has('seo_brief_en') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">{{ old('seo_brief_en', $aboutUs->seo_brief_en ?? '') }}</textarea>
        @error('seo_brief_en')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2 xl:col-span-2">
        <label for="seo_brief_ar" class="text-sm font-semibold text-slate-700">SEO Brief (AR)</label>
        <textarea id="seo_brief_ar" name="seo_brief_ar" rows="3"
            class="w-full rounded-2xl border {{ $errors->has('seo_brief_ar') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">{{ old('seo_brief_ar', $aboutUs->seo_brief_ar ?? '') }}</textarea>
        @error('seo_brief_ar')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2 xl:col-span-2">
        <label for="first_section_en" class="text-sm font-semibold text-slate-700">First Section (EN)</label>
        <textarea id="first_section_en" name="first_section_en" rows="5"
            class="w-full rounded-2xl border {{ $errors->has('first_section_en') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">{{ old('first_section_en', $aboutUs->first_section_en ?? '') }}</textarea>
        @error('first_section_en')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2 xl:col-span-2">
        <label for="first_section_ar" class="text-sm font-semibold text-slate-700">First Section (AR)</label>
        <textarea id="first_section_ar" name="first_section_ar" rows="5"
            class="w-full rounded-2xl border {{ $errors->has('first_section_ar') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">{{ old('first_section_ar', $aboutUs->first_section_ar ?? '') }}</textarea>
        @error('first_section_ar')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="mission_en" class="text-sm font-semibold text-slate-700">Mission (EN)</label>
        <textarea id="mission_en" name="mission_en" rows="5"
            class="w-full rounded-2xl border {{ $errors->has('mission_en') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">{{ old('mission_en', $aboutUs->mission_en ?? '') }}</textarea>
        @error('mission_en')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="mission_ar" class="text-sm font-semibold text-slate-700">Mission (AR)</label>
        <textarea id="mission_ar" name="mission_ar" rows="5"
            class="w-full rounded-2xl border {{ $errors->has('mission_ar') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">{{ old('mission_ar', $aboutUs->mission_ar ?? '') }}</textarea>
        @error('mission_ar')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="vision_en" class="text-sm font-semibold text-slate-700">Vision (EN)</label>
        <textarea id="vision_en" name="vision_en" rows="5"
            class="w-full rounded-2xl border {{ $errors->has('vision_en') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">{{ old('vision_en', $aboutUs->vision_en ?? '') }}</textarea>
        @error('vision_en')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="vision_ar" class="text-sm font-semibold text-slate-700">Vision (AR)</label>
        <textarea id="vision_ar" name="vision_ar" rows="5"
            class="w-full rounded-2xl border {{ $errors->has('vision_ar') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">{{ old('vision_ar', $aboutUs->vision_ar ?? '') }}</textarea>
        @error('vision_ar')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
    <button type="submit"
        class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]">
        <i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>
        {{ $submitLabel ?? 'Save Record' }}
    </button>

    <a href="{{ route('admin.about-us') }}"
        class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
        <i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>
        Back to List
    </a>
</div>
