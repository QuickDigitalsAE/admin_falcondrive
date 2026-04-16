@php
    $selectedCategoryIds = collect(old('category_ids', isset($car) ? $car->categories->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $selectedCategoryMap = collect($categories ?? [])
        ->whereIn('id', $selectedCategoryIds)
        ->map(fn ($category) => ['id' => (int) $category->id, 'name' => $category->name_en])
        ->values()
        ->all();
@endphp

@csrf

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    @foreach ([
        ['id' => 'name_en', 'label' => 'Name EN', 'value' => old('name_en', $car?->name_en ?? '')],
        ['id' => 'name_ar', 'label' => 'Name AR', 'value' => old('name_ar', $car?->name_ar ?? '')],
        ['id' => 'slug', 'label' => 'Slug', 'value' => old('slug', $car?->slug ?? '')],
        ['id' => 'model', 'label' => 'Model', 'value' => old('model', $car?->model ?? '')],
        ['id' => 'price_daily', 'label' => 'Price Daily', 'value' => old('price_daily', $car?->price_daily ?? '')],
        ['id' => 'price_weekly', 'label' => 'Price Weekly', 'value' => old('price_weekly', $car?->price_weekly ?? '')],
        ['id' => 'price_monthly', 'label' => 'Price Monthly', 'value' => old('price_monthly', $car?->price_monthly ?? '')],
        ['id' => 'engine', 'label' => 'Engine', 'value' => old('engine', $car?->engine ?? '')],
        ['id' => 'seats', 'label' => 'Seats', 'value' => old('seats', $car?->seats ?? '')],
        ['id' => 'doors', 'label' => 'Doors', 'value' => old('doors', $car?->doors ?? '')],
        ['id' => 'deposit', 'label' => 'Deposit', 'value' => old('deposit', $car?->deposit ?? '')],
        ['id' => 'luggage', 'label' => 'Luggage', 'value' => old('luggage', $car?->luggage ?? '')],
        ['id' => 'cdw_daily', 'label' => 'CDW Daily', 'value' => old('cdw_daily', $car?->cdw_daily ?? '')],
        ['id' => 'cdw_weekly', 'label' => 'CDW Weekly', 'value' => old('cdw_weekly', $car?->cdw_weekly ?? '')],
        ['id' => 'cdw_monthly', 'label' => 'CDW Monthly', 'value' => old('cdw_monthly', $car?->cdw_monthly ?? '')],
        ['id' => 'sorting', 'label' => 'Sorting', 'value' => old('sorting', $car?->sorting ?? '')],
        ['id' => 'seo_title_en', 'label' => 'SEO Title EN', 'value' => old('seo_title_en', $car?->seo_title_en ?? '')],
        ['id' => 'seo_title_ar', 'label' => 'SEO Title AR', 'value' => old('seo_title_ar', $car?->seo_title_ar ?? '')],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input id="{{ $field['id'] }}" type="text" name="{{ $field['id'] }}" value="{{ $field['value'] }}" placeholder="{{ $field['label'] }}"
                        class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">{{ $field['label'] }}</label>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach

    <div class="min-w-0">
        <div class="space-y-2">
            <div class="relative">
                <select id="brand_id" name="brand_id"
                    class="peer w-full appearance-none rounded-[18px] border {{ $errors->has('brand_id') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 pr-11 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <option value="">Select Brand</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" {{ old('brand_id', $car?->brand_id ?? '') == $brand->id ? 'selected' : '' }}>{{ $brand->name_en }}</option>
                    @endforeach
                </select>
                <label for="brand_id" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('brand_id') ? 'text-red-500' : 'text-slate-500' }}">Brand</label>
                <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
            </div>
            @error('brand_id')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    @foreach ([
        ['id' => 'featured', 'label' => 'Featured', 'value' => old('featured', (int) ($car?->featured ?? 0))],
        ['id' => 'cruise_control', 'label' => 'Cruise Control', 'value' => old('cruise_control', (int) ($car?->cruise_control ?? 0))],
        ['id' => 'bluetooth', 'label' => 'Bluetooth', 'value' => old('bluetooth', (int) ($car?->bluetooth ?? 0))],
        ['id' => 'automatic', 'label' => 'Automatic', 'value' => old('automatic', (int) ($car?->automatic ?? 0))],
        ['id' => 'parking_sensor', 'label' => 'Parking Sensor', 'value' => old('parking_sensor', (int) ($car?->parking_sensor ?? 0))],
        ['id' => 'navigation', 'label' => 'Navigation', 'value' => old('navigation', (int) ($car?->navigation ?? 0))],
        ['id' => 'carplay', 'label' => 'CarPlay', 'value' => old('carplay', (int) ($car?->carplay ?? 0))],
        ['id' => 'camera', 'label' => 'Camera', 'value' => old('camera', (int) ($car?->camera ?? 0))],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <select id="{{ $field['id'] }}" name="{{ $field['id'] }}"
                        class="peer w-full appearance-none rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 pr-11 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                        <option value="0" {{ (string) $field['value'] === '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ (string) $field['value'] === '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500' }}">{{ $field['label'] }}</label>
                    <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach

    <div class="xl:col-span-3 min-w-0">
        <div class="space-y-2">
            <div class="rounded-[24px] border {{ $errors->has('category_ids') ? 'border-red-300' : 'border-[#eadfbe]' }} bg-gradient-to-br from-[#fffdf8] to-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#b4861f]">Categories</p>
                        <p class="mt-1 text-sm text-slate-500">Choose a category from the dropdown and it will be added as a chip.</p>
                    </div>
                    <span class="rounded-full bg-[#f8e8b2] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#7d6220]">Relation</span>
                </div>
                <div id="selectedCategoryTags" class="car-chip-wrap mt-4 flex min-h-[64px] flex-wrap items-start gap-2 rounded-[20px] border border-[#f0e6ca] bg-white px-3 py-3" data-selected='@json($selectedCategoryMap)'></div>
                <div class="mt-4">
                    <div id="category_picker_wrap" class="relative">
                        <button id="category_picker_button" type="button"
                            class="flex w-full items-center justify-between rounded-[18px] border border-[#e5d7b1] bg-white px-4 py-3 text-left text-sm text-slate-800 shadow-sm transition duration-200 hover:border-[#d8bf72] focus:border-[#caa23c] focus:outline-none focus:ring-4 focus:ring-[#f7e9b5]">
                            <span id="category_picker_label">Select category to add</span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                        </button>
                        <div id="category_picker_panel" class="absolute left-0 right-0 top-[calc(100%+10px)] z-20 hidden overflow-hidden rounded-[20px] border border-[#eadfbe] bg-white shadow-[0_16px_40px_rgba(15,23,42,0.12)]">
                            <div class="border-b border-[#f0e6ca] p-3">
                                <label for="category_picker_search" class="sr-only">Search category</label>
                                <input id="category_picker_search" type="text" placeholder="Search category..."
                                    class="w-full rounded-[14px] border border-[#e5d7b1] bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition duration-200 focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                            </div>
                            <div id="category_picker_list" class="max-h-64 overflow-y-auto p-2"></div>
                        </div>
                    </div>
                </div>
                <select id="category_ids" name="category_ids[]" multiple
                    class="hidden">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ in_array($category->id, $selectedCategoryIds, true) ? 'selected' : '' }}>{{ $category->name_en }}</option>
                    @endforeach
                </select>
            </div>
            @error('category_ids')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            @error('category_ids.*')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="xl:col-span-3 min-w-0">
        <div class="space-y-2">
            <label for="description_en" class="block px-1 text-xs font-medium {{ $errors->has('description_en') ? 'text-red-500' : 'text-slate-500' }}">Description EN</label>
            <div class="resource-ckeditor-shell {{ $errors->has('description_en') ? 'is-invalid' : '' }}">
                <textarea id="description_en" name="description_en" rows="8">{{ old('description_en', $car?->description_en ?? '') }}</textarea>
            </div>
            @error('description_en')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="xl:col-span-3 min-w-0">
        <div class="space-y-2">
            <label for="description_ar" class="block px-1 text-xs font-medium {{ $errors->has('description_ar') ? 'text-red-500' : 'text-slate-500' }}">Description AR</label>
            <div class="resource-ckeditor-shell {{ $errors->has('description_ar') ? 'is-invalid' : '' }}">
                <textarea id="description_ar" name="description_ar" rows="8">{{ old('description_ar', $car?->description_ar ?? '') }}</textarea>
            </div>
            @error('description_ar')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0 xl:col-span-3 grid grid-cols-1 gap-5 xl:grid-cols-2">
        @foreach ([
            ['id' => 'seo_brief_en', 'label' => 'SEO Brief EN', 'value' => old('seo_brief_en', $car?->seo_brief_en ?? '')],
            ['id' => 'seo_brief_ar', 'label' => 'SEO Brief AR', 'value' => old('seo_brief_ar', $car?->seo_brief_ar ?? '')],
        ] as $field)
            <div class="min-w-0">
                <div class="space-y-2">
                    <div class="relative">
                        <textarea id="{{ $field['id'] }}" name="{{ $field['id'] }}" rows="4" placeholder="{{ $field['label'] }}"
                            class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ $field['value'] }}</textarea>
                        <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">{{ $field['label'] }}</label>
                    </div>
                    @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-4 sm:p-5">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
            <div class="flex shrink-0 items-center gap-4">
                <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-[#d8c79d] bg-white shadow-sm">
                    <img id="mainImagePreview" src="{{ $car?->main_image_url ?? 'https://placehold.co/200x200/f8e8b2/5e450a?text=Main' }}" alt="Main Preview" class="h-full w-full object-cover">
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Main Image</h3>
                    <p class="mt-1 text-sm text-slate-500">Upload the featured car image.</p>
                </div>
            </div>
            <div class="flex-1">
                <label for="main_image" class="group flex cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed {{ $errors->has('main_image') ? 'border-red-300 bg-red-50/40' : 'border-[#d8c79d] bg-white hover:border-[#c79a2b] hover:bg-[#fff8e8]' }} px-5 py-8 text-center transition">
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-image text-lg"></i></div>
                    <p class="text-sm font-semibold text-slate-800">Click to upload main image</p>
                    <p class="mt-1 text-xs text-slate-500">PNG, JPG, JPEG, WEBP up to 4MB</p>
                    <input id="main_image" type="file" name="main_image" accept=".png,.jpg,.jpeg,.webp" class="hidden">
                </label>
                <div class="mt-3"><span id="mainImageFileName" class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 shadow-sm ring-1 ring-[#eadfbe]">{{ !empty($car?->main_image) ? basename($car->main_image) : 'No file selected' }}</span></div>
                @error('main_image')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-4 sm:p-5">
        <div>
            <h3 class="text-sm font-semibold text-slate-800">Gallery Images</h3>
            <p class="mt-1 text-sm text-slate-500">Upload multiple supporting images for the car gallery.</p>
        </div>
        <label for="images" class="mt-4 group flex cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed {{ $errors->has('images') || $errors->has('images.*') ? 'border-red-300 bg-red-50/40' : 'border-[#d8c79d] bg-white hover:border-[#c79a2b] hover:bg-[#fff8e8]' }} px-5 py-8 text-center transition">
            <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-images text-lg"></i></div>
            <p class="text-sm font-semibold text-slate-800">Click to upload gallery images</p>
            <p class="mt-1 text-xs text-slate-500">You can select multiple images at once</p>
            <input id="images" type="file" name="images[]" accept=".png,.jpg,.jpeg,.webp" multiple class="hidden">
        </label>
        <div class="mt-3"><span id="galleryFileName" class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 shadow-sm ring-1 ring-[#eadfbe]">{{ !empty($car?->images) ? count($car->images) . ' image(s) selected' : 'No files selected' }}</span></div>
        @error('images')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        @error('images.*')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        <div id="galleryPreviewGrid" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4"></div>
    </div>
</div>

<div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-4 sm:p-5">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#b4861f]">Stock</p>
            <h3 class="mt-1 text-lg font-semibold text-slate-900">Availability Status</h3>
            <p class="mt-1 text-sm text-slate-500">Turn this on when the car is available for listing and booking.</p>
        </div>
        <div class="flex items-center gap-4 rounded-[20px] border border-[#eadfbe] bg-white px-4 py-3 shadow-sm">
            <span id="stockToggleLabel" class="text-sm font-semibold {{ old('stock', (int) ($car?->stock ?? 0)) ? 'text-emerald-700' : 'text-slate-500' }}">
                {{ old('stock', (int) ($car?->stock ?? 0)) ? 'On' : 'Off' }}
            </span>
            <label for="stock" class="relative inline-flex cursor-pointer items-center">
                <input type="hidden" name="stock" value="0">
                <input id="stock" type="checkbox" name="stock" value="1" class="peer sr-only" {{ old('stock', (int) ($car?->stock ?? 0)) ? 'checked' : '' }}>
                <span class="h-8 w-16 rounded-full bg-slate-200 transition peer-checked:bg-emerald-500 peer-focus:ring-4 peer-focus:ring-emerald-100"></span>
                <span class="absolute left-1 h-6 w-6 rounded-full bg-white shadow-sm transition peer-checked:translate-x-8"></span>
            </label>
        </div>
    </div>
    @error('stock')<p class="mt-3 px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
</div>

<div class="rounded-[22px] border border-dashed border-[#eadfbe] bg-[#fffdf8] px-4 py-4 sm:px-5">
    <div class="flex items-start gap-3">
        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-circle-info text-sm"></i></div>
        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-slate-800">Before saving</h3>
            <p class="mt-1 text-sm text-slate-500">Check brand and category mapping, keep pricing fields consistent, and use the rich descriptions for full public listing content.</p>
        </div>
    </div>
</div>

<div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]">
        <i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>{{ $submitLabel }}
    </button>
    @if (!empty($car?->id))
        <a href="{{ route('admin.cars.show', $car->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Car</a>
    @endif
    <a href="{{ route('admin.cars') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a>
</div>
