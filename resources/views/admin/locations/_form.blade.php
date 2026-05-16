@php
    $selectedCarIds = collect(old('car_ids', isset($location) ? $location->cars->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

@csrf
<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    @foreach ([
        ['id' => 'name_en', 'label' => 'Name EN'],
        ['id' => 'name_ar', 'label' => 'Name AR'],
        ['id' => 'slug', 'label' => 'Slug'],
        ['id' => 'seo_title_en', 'label' => 'SEO Title EN'],
        ['id' => 'seo_title_ar', 'label' => 'SEO Title AR'],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input id="{{ $field['id'] }}" type="text" name="{{ $field['id'] }}" value="{{ old($field['id'], $location->{$field['id']} ?? '') }}" placeholder="{{ $field['label'] }}"
                        class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">{{ $field['label'] }}</label>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach

    <div class="xl:col-span-3 min-w-0">
        <div class="space-y-2">
            <div class="rounded-[22px] border {{ $errors->has('car_ids') ? 'border-red-300' : 'border-[#eadfbe]' }} bg-[#fffdf8] p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#b4861f]">Cars</p>
                        <p class="mt-1 text-sm text-slate-500">Choose cars from the dropdown and they will be added as chips.</p>
                    </div>
                    <span class="rounded-full bg-[#f8e8b2] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#7d6220]">Relation</span>
                </div>
                <div id="selectedCarTags" class="mt-3 flex flex-wrap gap-2"></div>
                <div class="mt-3">
                    <div id="car_picker_wrap" class="relative">
                        <button id="car_picker_button" type="button"
                            class="admin-picker-trigger flex w-full items-center justify-between rounded-[18px] px-4 py-3 text-left text-sm text-slate-800 transition duration-200 hover:border-[#d8bf72] focus:border-[#caa23c] focus:outline-none focus:ring-4 focus:ring-[#f7e9b5]">
                            <span id="car_picker_label">Select car to add</span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                        </button>
                        <div id="car_picker_panel" class="admin-picker-panel absolute left-0 right-0 top-[calc(100%+10px)] z-20 hidden rounded-[20px]">
                            <div class="border-b border-[#f0e6ca] p-3">
                                <label for="car_picker_search" class="sr-only">Search car</label>
                                <input id="car_picker_search" type="text" placeholder="Search car..."
                                    class="admin-picker-search w-full rounded-[14px] px-4 py-3 text-sm text-slate-800 outline-none transition duration-200 focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                            </div>
                            <div id="car_picker_list" class="max-h-64 overflow-y-auto p-2"></div>
                        </div>
                    </div>
                </div>
                <select id="car_ids" name="car_ids[]" multiple class="hidden">
                    @foreach ($cars as $car)
                        <option value="{{ $car->id }}" {{ in_array($car->id, $selectedCarIds, true) ? 'selected' : '' }}>
                            {{ $car->name_en }}{{ $car->model ? ' (' . $car->model . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('car_ids')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            @error('car_ids.*')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0 xl:col-span-3 grid grid-cols-1 gap-5 xl:grid-cols-2">
        @foreach ([
            ['id' => 'seo_brief_en', 'label' => 'SEO Brief EN', 'rows' => 4],
            ['id' => 'seo_brief_ar', 'label' => 'SEO Brief AR', 'rows' => 4],
        ] as $field)
            <div class="min-w-0">
                <div class="space-y-2">
                    <div class="relative">
                        <textarea id="{{ $field['id'] }}" name="{{ $field['id'] }}" rows="{{ $field['rows'] }}" placeholder="{{ $field['label'] }}"
                            class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ old($field['id'], $location->{$field['id']} ?? '') }}</textarea>
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
                <textarea id="description_en" name="description_en" rows="5">{{ old('description_en', $location->description_en ?? '') }}</textarea>
            </div>
            @error('description_en')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="min-w-0 xl:col-span-3">
        <div class="space-y-2">
            <label for="description_ar" class="block px-1 text-xs font-medium {{ $errors->has('description_ar') ? 'text-red-500' : 'text-slate-500' }}">Description AR</label>
            <div class="resource-ckeditor-shell {{ $errors->has('description_ar') ? 'is-invalid' : '' }}">
                <textarea id="description_ar" name="description_ar" rows="5">{{ old('description_ar', $location->description_ar ?? '') }}</textarea>
            </div>
            @error('description_ar')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
<div class="rounded-[22px] border border-dashed border-[#eadfbe] bg-[#fffdf8] px-4 py-4 sm:px-5"><div class="flex items-start gap-3"><div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-circle-info text-sm"></i></div><div class="min-w-0"><h3 class="text-sm font-semibold text-slate-800">Before saving</h3><p class="mt-1 text-sm text-slate-500">Use a readable location name, keep the slug unique, and match SEO text with the destination content.</p></div></div></div>
<div class="mt-6 flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap"><button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white"><i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>{{ $submitLabel }}</button>@if(!empty($location?->id))<a href="{{ route('admin.locations.show', $location->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Location</a>@endif<a href="{{ route('admin.locations') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a></div>
