@csrf
<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    @foreach ([
        ['id' => 'name_en', 'label' => 'Name EN'],
        ['id' => 'name_ar', 'label' => 'Name AR'],
    ] as $field)
        <div class="min-w-0">
            <div class="space-y-2">
                <div class="relative">
                    <input id="{{ $field['id'] }}" type="text" name="{{ $field['id'] }}" value="{{ old($field['id'], $testimonial->{$field['id']} ?? '') }}" placeholder="{{ $field['label'] }}"
                        class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has($field['id']) ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                    <label for="{{ $field['id'] }}" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">{{ $field['label'] }}</label>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach

    @foreach ([
        ['id' => 'description_en', 'label' => 'Description EN'],
        ['id' => 'description_ar', 'label' => 'Description AR'],
    ] as $field)
        <div class="min-w-0 xl:col-span-2">
            <div class="space-y-2">
                <label for="{{ $field['id'] }}" class="block px-1 text-xs font-medium {{ $errors->has($field['id']) ? 'text-red-500' : 'text-slate-500' }}">{{ $field['label'] }}</label>
                <div class="resource-ckeditor-shell {{ $errors->has($field['id']) ? 'is-invalid' : '' }}">
                    <textarea id="{{ $field['id'] }}" name="{{ $field['id'] }}" rows="5">{{ old($field['id'], $testimonial->{$field['id']} ?? '') }}</textarea>
                </div>
                @error($field['id'])<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    @endforeach
</div>

<div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-4 sm:p-5">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
        <div class="flex shrink-0 items-center gap-4">
            <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-[#d8c79d] bg-white shadow-sm">
                <img id="testimonialPreview" src="{{ $testimonial?->image_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($testimonial->name_en ?? 'Testimonial') . '&background=F8E8B2&color=5E450A&size=200' }}" alt="Testimonial Preview" class="h-full w-full object-cover">
            </div>
            <div><h3 class="text-sm font-semibold text-slate-800">Testimonial Image</h3><p class="mt-1 text-sm text-slate-500">Upload a JPG, PNG, or WEBP image for this testimonial.</p></div>
        </div>
        <div class="flex-1">
            <label for="image" class="group flex cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed {{ $errors->has('image') ? 'border-red-300 bg-red-50/40' : 'border-[#d8c79d] bg-white hover:border-[#c79a2b] hover:bg-[#fff8e8]' }} px-5 py-8 text-center transition">
                <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-cloud-arrow-up text-lg"></i></div>
                <p class="text-sm font-semibold text-slate-800">Click to upload testimonial image</p>
                <p class="mt-1 text-xs text-slate-500">PNG, JPG, JPEG, WEBP up to 2MB</p>
                <input id="image" type="file" name="image" accept=".png,.jpg,.jpeg,.webp" class="hidden">
            </label>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <span id="fileName" class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 shadow-sm ring-1 ring-[#eadfbe]">{{ !empty($testimonial?->image) ? basename($testimonial->image) : 'No file selected' }}</span>
                @if(!empty($testimonial?->image_url))
                    <label class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-3 py-2 text-xs font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><input type="checkbox" name="remove_image" value="1" class="mr-2">Remove Current</label>
                @endif
                <button type="button" id="removeImageBtn" class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-3 py-2 text-xs font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-trash-can mr-2"></i>Remove</button>
            </div>
            @error('image')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>{{ $submitLabel }}</button>
    @if(!empty($testimonial?->id))
        <a href="{{ route('admin.testimonials.show', $testimonial->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Testimonial</a>
    @endif
    <a href="{{ route('admin.testimonials') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a>
</div>
