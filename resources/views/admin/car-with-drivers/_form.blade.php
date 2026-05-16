@php
    $selectedCarIds = collect(old('car_ids', isset($record) ? $record->carsRelation->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

@csrf

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    @foreach ([
        ['id' => 'display_en', 'label' => 'Display EN', 'value' => old('display_en', $record?->display_en ?? '')],
        ['id' => 'display_ar', 'label' => 'Display AR', 'value' => old('display_ar', $record?->display_ar ?? '')],
        ['id' => 'slug', 'label' => 'Slug', 'value' => old('slug', $record?->slug ?? '')],
        ['id' => 'header_en', 'label' => 'Header EN', 'value' => old('header_en', $record?->header_en ?? '')],
        ['id' => 'header_ar', 'label' => 'Header AR', 'value' => old('header_ar', $record?->header_ar ?? '')],
        ['id' => 'meta_title_en', 'label' => 'Meta Title EN', 'value' => old('meta_title_en', $record?->meta_title_en ?? '')],
        ['id' => 'meta_title_ar', 'label' => 'Meta Title AR', 'value' => old('meta_title_ar', $record?->meta_title_ar ?? '')],
        ['id' => 'card_header_en', 'label' => 'Card Header EN', 'value' => old('card_header_en', $record?->card_header_en ?? '')],
        ['id' => 'card_header_ar', 'label' => 'Card Header AR', 'value' => old('card_header_ar', $record?->card_header_ar ?? '')],
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

    @foreach ([
        ['id' => 'meta_description_en', 'label' => 'Meta Description EN', 'value' => old('meta_description_en', $record?->meta_description_en ?? '')],
        ['id' => 'meta_description_ar', 'label' => 'Meta Description AR', 'value' => old('meta_description_ar', $record?->meta_description_ar ?? '')],
        ['id' => 'card_text_en', 'label' => 'Card Text EN', 'value' => old('card_text_en', $record?->card_text_en ?? '')],
        ['id' => 'card_text_ar', 'label' => 'Card Text AR', 'value' => old('card_text_ar', $record?->card_text_ar ?? '')],
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

    <div class="xl:col-span-2 min-w-0">
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

    <div class="xl:col-span-2 rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-4 sm:p-5">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
            <div class="flex shrink-0 items-center gap-4">
                <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-[#d8c79d] bg-white shadow-sm">
                    <img id="cardImagePreview" src="{{ $record?->card_image_url ?? 'https://placehold.co/200x200/f8e8b2/5e450a?text=Card' }}" alt="Card Preview" class="h-full w-full object-cover">
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Card Image</h3>
                    <p class="mt-1 text-sm text-slate-500">Upload the card image for this section.</p>
                </div>
            </div>
            <div class="flex-1">
                <label for="card_image" class="group flex cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed {{ $errors->has('card_image') ? 'border-red-300 bg-red-50/40' : 'border-[#d8c79d] bg-white hover:border-[#c79a2b] hover:bg-[#fff8e8]' }} px-5 py-8 text-center transition">
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-image text-lg"></i></div>
                    <p class="text-sm font-semibold text-slate-800">Click to upload card image</p>
                    <p class="mt-1 text-xs text-slate-500">PNG, JPG, JPEG, WEBP up to 4MB</p>
                    <input id="card_image" type="file" name="card_image" accept=".png,.jpg,.jpeg,.webp" class="hidden">
                </label>
                <div class="mt-3"><span id="cardImageFileName" class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 shadow-sm ring-1 ring-[#eadfbe]">{{ !empty($record?->card_image) ? basename($record->card_image) : 'No file selected' }}</span></div>
                @error('card_image')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="xl:col-span-2 min-w-0">
        <div class="space-y-2">
            <label for="content_en" class="block px-1 text-xs font-medium {{ $errors->has('content_en') ? 'text-red-500' : 'text-slate-500' }}">Content EN</label>
            <div class="cwd-ckeditor-shell {{ $errors->has('content_en') ? 'is-invalid' : '' }}">
                <textarea id="content_en" name="content_en" rows="8">{{ old('content_en', $record?->content_en ?? '') }}</textarea>
            </div>
            @error('content_en')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="xl:col-span-2 min-w-0">
        <div class="space-y-2">
            <label for="content_ar" class="block px-1 text-xs font-medium {{ $errors->has('content_ar') ? 'text-red-500' : 'text-slate-500' }}">Content AR</label>
            <div class="cwd-ckeditor-shell {{ $errors->has('content_ar') ? 'is-invalid' : '' }}">
                <textarea id="content_ar" name="content_ar" rows="8">{{ old('content_ar', $record?->content_ar ?? '') }}</textarea>
            </div>
            @error('content_ar')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="rounded-[22px] border border-dashed border-[#eadfbe] bg-[#fffdf8] px-4 py-4 sm:px-5">
    <div class="flex items-start gap-3">
        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-circle-info text-sm"></i></div>
        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-slate-800">Before saving</h3>
            <p class="mt-1 text-sm text-slate-500">Check SEO fields, card content, and related car mapping before publishing this car with driver section.</p>
        </div>
    </div>
</div>

<div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]">
        <i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>{{ $submitLabel }}
    </button>
    @if (!empty($record?->id))
        <a href="{{ route('admin.car-with-drivers.show', $record->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Record</a>
    @endif
    <a href="{{ route('admin.car-with-drivers') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a>
</div>
