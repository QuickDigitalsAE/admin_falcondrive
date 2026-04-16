@csrf

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    @foreach ([
        ['id' => 'name_en', 'label' => 'Name EN', 'help' => 'Primary category name', 'required' => true],
        ['id' => 'name_ar', 'label' => 'Name AR', 'help' => 'Localized category name', 'required' => true],
        ['id' => 'slug', 'label' => 'Slug', 'help' => 'Used in URLs and SEO routing', 'required' => true],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input id="{{ $field['id'] }}" type="text" name="{{ $field['id'] }}" value="{{ old($field['id'], $category->{$field['id']} ?? '') }}" placeholder="{{ $field['label'] }}"
                        class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">{{ $field['label'] }}</label>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 px-1"><p class="text-xs text-slate-500">{{ $field['help'] }}</p><span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#b4861f]">{{ $field['required'] ? 'Required' : 'Optional' }}</span></div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach

    <div class="min-w-0 xl:col-span-3 grid grid-cols-1 gap-5 xl:grid-cols-2">
        @foreach ([
            ['id' => 'seo_brief_en', 'label' => 'SEO Brief EN', 'rows' => 4],
            ['id' => 'seo_brief_ar', 'label' => 'SEO Brief AR', 'rows' => 4],
        ] as $field)
            <div class="min-w-0">
                <div class="space-y-2">
                    <div class="relative">
                        <textarea id="{{ $field['id'] }}" name="{{ $field['id'] }}" rows="{{ $field['rows'] }}" placeholder="{{ $field['label'] }}"
                            class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ old($field['id'], $category->{$field['id']} ?? '') }}</textarea>
                        <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">{{ $field['label'] }}</label>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="min-w-0 xl:col-span-3">
        <div class="space-y-2">
            <label for="description_en" class="block px-1 text-xs font-medium {{ $errors->has('description_en') ? 'text-red-500' : 'text-slate-500' }}">Description EN</label>
            <div class="resource-ckeditor-shell {{ $errors->has('description_en') ? 'is-invalid' : '' }}">
                <textarea id="description_en" name="description_en" rows="5">{{ old('description_en', $category->description_en ?? '') }}</textarea>
            </div>
            @error('description_en')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0 xl:col-span-3">
        <div class="space-y-2">
            <label for="description_ar" class="block px-1 text-xs font-medium {{ $errors->has('description_ar') ? 'text-red-500' : 'text-slate-500' }}">Description AR</label>
            <div class="resource-ckeditor-shell {{ $errors->has('description_ar') ? 'is-invalid' : '' }}">
                <textarea id="description_ar" name="description_ar" rows="5">{{ old('description_ar', $category->description_ar ?? '') }}</textarea>
            </div>
            @error('description_ar')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    @foreach ([
        ['id' => 'seo_title_en', 'label' => 'SEO Title EN'],
        ['id' => 'seo_title_ar', 'label' => 'SEO Title AR'],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input id="{{ $field['id'] }}" type="text" name="{{ $field['id'] }}" value="{{ old($field['id'], $category->{$field['id']} ?? '') }}" placeholder="{{ $field['label'] }}"
                        class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">{{ $field['label'] }}</label>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="rounded-[22px] border border-dashed border-[#eadfbe] bg-[#fffdf8] px-4 py-4 sm:px-5">
    <div class="flex items-start gap-3">
        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-circle-info text-sm"></i></div>
        <div class="min-w-0"><h3 class="text-sm font-semibold text-slate-800">Before saving</h3><p class="mt-1 text-sm text-slate-500">Keep category names clear, use a unique slug, and fill SEO text if this category will be public.</p></div>
    </div>
</div>

<div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>{{ $submitLabel }}</button>
    @if(!empty($category?->id))
        <a href="{{ route('admin.categories.show', $category->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Category</a>
    @endif
    <a href="{{ route('admin.categories') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a>
</div>
