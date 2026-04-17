@php
    $typeDocs = [
        'text' => 'Best for short labels, IDs, phone numbers, and plain one-line values.',
        'textarea' => 'Best for paragraphs, notices, footer text, and longer plain content.',
        'boolean' => 'Stores a simple on/off value as 1 or 0.',
        'number' => 'Best for counters, limits, sorting thresholds, and numeric config.',
        'url' => 'Best for links to pages, forms, APIs, maps, or social platforms.',
        'email' => 'Best for mailbox settings such as support or sales contact emails.',
        'json' => 'Best for structured options, arrays, nested config, and API-ready data.',
        'color' => 'Best for theme colors, brand accents, and visual settings like #C79A2B.',
        'html' => 'Best for embed code, snippets, banners, or trusted rich markup.',
        'rich_text_box' => 'Best for formatted content with headings, lists, links, tables, and rich text editing.',
        'image' => 'Best for logos, hero images, badges, app icons, and visual website assets.',
        'file' => 'Best for brochures, PDFs, policy docs, downloadable files, and attachments.',
    ];
    $currentType = old('type', $setting->type ?? 'text');
    $currentValue = old('value', $setting->value ?? '');
    $isCurrentImage = $currentType === 'image' && !empty($currentValue);
    $isCurrentFile = $currentType === 'file' && !empty($currentValue);
    $fallbackAssetUrl = !empty($currentValue)
        ? (filter_var($currentValue, FILTER_VALIDATE_URL)
            ? $currentValue
            : asset('storage/' . ltrim(str_replace('\\', '/', $currentValue), '/')))
        : null;
@endphp

@csrf

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <div class="min-w-0">
                <div class="space-y-2">
                    <div class="relative">
                        <input id="display_name" type="text" name="display_name" value="{{ old('display_name', $setting->display_name ?? '') }}" placeholder="Display Name"
                            class="peer w-full rounded-[18px] border {{ $errors->has('display_name') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                        <label for="display_name" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('display_name') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">Display Name</label>
                    </div>
                    @error('display_name')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="min-w-0">
                <div class="space-y-2">
                    <div class="relative">
                        <input id="key" type="text" name="key" value="{{ old('key', $setting->key ?? '') }}" placeholder="Key"
                            class="peer w-full rounded-[18px] border {{ $errors->has('key') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                        <label for="key" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('key') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">Key</label>
                    </div>
                    <p class="px-1 text-xs text-slate-500">Use a stable machine key like `contact.phone`, `seo.meta_title`, or `homepage_banner_text`.</p>
                    @error('key')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="min-w-0">
                <div class="space-y-2">
                    <div class="relative">
                        <select id="group" name="group"
                            class="peer w-full appearance-none rounded-[18px] border {{ $errors->has('group') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 pr-11 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                            @foreach ($groups as $group)
                                <option value="{{ $group }}" {{ old('group', $setting->group ?? 'site') === $group ? 'selected' : '' }}>{{ ucfirst($group) }}</option>
                            @endforeach
                        </select>
                        <label for="group" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">Group</label>
                        <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
                    </div>
                    @error('group')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="min-w-0">
                <div class="space-y-2">
                    <div class="relative">
                        <input id="order" type="text" name="order" value="{{ old('order', $setting->order ?? 1) }}" placeholder="Order"
                            class="peer w-full rounded-[18px] border {{ $errors->has('order') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                        <label for="order" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('order') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">Order</label>
                    </div>
                    @error('order')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="min-w-0 xl:col-span-2">
                <div class="space-y-2">
                    <div class="relative">
                        <select id="type" name="type"
                            class="peer w-full appearance-none rounded-[18px] border {{ $errors->has('type') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 pr-11 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                            @foreach ($settingTypes as $settingType)
                                <option value="{{ $settingType }}" {{ old('type', $setting->type ?? 'text') === $settingType ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $settingType)) }}</option>
                            @endforeach
                        </select>
                        <label for="type" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">Value Type</label>
                        <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400"><i class="fa-solid fa-chevron-down text-xs"></i></div>
                    </div>
                    @error('type')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#b4861f]">Value Workspace</p>
                    <h3 class="mt-1 text-lg font-semibold text-slate-900">Setting Value</h3>
                    <p id="typeDescription" class="mt-1 text-sm text-slate-500">{{ $typeDocs[old('type', $setting->type ?? 'text')] ?? $typeDocs['text'] }}</p>
                </div>
                <span id="typeBadge" class="inline-flex rounded-full bg-[#fff4d6] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#7d6220]">{{ old('type', $setting->type ?? 'text') }}</span>
            </div>

            <div class="mt-4 space-y-4">
                <div id="valueSingleWrap" class="hidden">
                    <div class="relative">
                        <input id="value_single" type="text" class="peer w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]" placeholder="Value">
                        <label for="value_single" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs peer-focus:text-[#a27d20]">Value</label>
                    </div>
                </div>

                <div id="valueBooleanWrap" class="hidden">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <button type="button" class="boolean-choice rounded-[20px] border border-[#eadfbe] bg-[#fffdf8] px-4 py-4 text-left transition hover:border-[#d6ab3d]" data-boolean-value="1">
                            <span class="block text-sm font-semibold text-slate-900">Enabled</span>
                            <span class="mt-1 block text-xs text-slate-500">Stores `1` for active or visible settings.</span>
                        </button>
                        <button type="button" class="boolean-choice rounded-[20px] border border-[#eadfbe] bg-[#fffdf8] px-4 py-4 text-left transition hover:border-[#d6ab3d]" data-boolean-value="0">
                            <span class="block text-sm font-semibold text-slate-900">Disabled</span>
                            <span class="mt-1 block text-xs text-slate-500">Stores `0` for inactive or hidden settings.</span>
                        </button>
                    </div>
                </div>

                <div id="valueAreaWrap">
                    <div id="valueEditorShell" class="resource-ckeditor-shell {{ $errors->has('value') ? 'is-invalid' : '' }}">
                        <textarea id="value" name="value" rows="8" placeholder="Value"
                            class="peer w-full rounded-[18px] border {{ $errors->has('value') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 font-mono text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4"
                            data-ckeditor-direction="ltr">{{ old('value', $setting->value ?? '') }}</textarea>
                        <label for="value" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] text-slate-500">Value</label>
                    </div>
                </div>

                <div id="valueAssetWrap" class="hidden space-y-4">
                    <div class="rounded-[20px] border border-dashed border-[#dcc58a] bg-[#fffdf6] p-4">
                        <label for="asset" class="block text-sm font-semibold text-slate-900">Choose File</label>
                        <p class="mt-1 text-xs text-slate-500">Each setting stores one uploaded asset. Create separate settings for each image or file you want to manage.</p>
                        <input id="asset" name="asset" type="file" class="mt-4 block w-full rounded-2xl border border-[#e5d7b1] bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-[#d6ab3d] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#c59626]">
                        <p id="assetHelpText" class="mt-2 text-xs text-slate-500">Upload the asset you want this setting to point to.</p>
                        @error('asset')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div id="assetCurrentWrap" class="{{ ($isCurrentImage || $isCurrentFile) ? '' : 'hidden ' }}rounded-[20px] border border-[#eadfbe] bg-white p-4">
                        <div class="flex flex-col gap-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-900">Current Asset</p>
                                @if (!empty($setting?->value_url))
                                    <a id="assetCurrentLink" href="{{ $setting->value_url }}" target="_blank" class="inline-flex items-center rounded-full bg-[#fff4d6] px-3 py-1 text-xs font-semibold text-[#7d6220]">
                                        <i class="fa-solid fa-arrow-up-right-from-square mr-2 text-[11px]"></i>Open
                                    </a>
                                @else
                                    <a id="assetCurrentLink" href="{{ !empty($currentValue) ? (filter_var($currentValue, FILTER_VALIDATE_URL) ? $currentValue : asset('storage/' . ltrim($currentValue, '/'))) : '#' }}" target="_blank" class="hidden inline-flex items-center rounded-full bg-[#fff4d6] px-3 py-1 text-xs font-semibold text-[#7d6220]">
                                        <i class="fa-solid fa-arrow-up-right-from-square mr-2 text-[11px]"></i>Open
                                    </a>
                                @endif
                            </div>
                            <img id="assetImagePreview" src="{{ !empty($setting?->value_url) && $currentType === 'image' ? $setting->value_url : '' }}" alt="Setting image preview" class="{{ $isCurrentImage ? '' : 'hidden ' }}max-h-56 w-full rounded-2xl border border-[#f0e6ca] object-contain bg-[#fffdf8] p-3">
                            <div id="assetFilePreview" class="{{ $isCurrentFile ? '' : 'hidden ' }}rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] px-4 py-3 text-sm text-slate-700">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#fff4d6] text-[#8b6717]"><i class="fa-solid fa-file-arrow-down"></i></span>
                                    <div class="min-w-0">
                                        <p id="assetFileName" class="truncate font-medium text-slate-900">{{ basename((string) $currentValue) }}</p>
                                        <p class="text-xs text-slate-500">Stored in this setting as the current file.</p>
                                    </div>
                                </div>
                            </div>
                            <p id="assetCurrentPath" class="break-all font-mono text-xs text-slate-500">{{ $currentValue }}</p>
                        </div>
                    </div>
                </div>

                @error('value')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#b4861f]">Inner Details</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-900">Optional Metadata</h3>
                <p class="mt-1 text-sm text-slate-500">Use this for notes, validation rules, placeholders, UI hints, or JSON configuration for the setting.</p>
            </div>
            <div class="mt-4">
                <textarea id="details" name="details" rows="8" placeholder='{"placeholder":"Enter phone","help":"Shown in header"}'
                    class="w-full rounded-[18px] border {{ $errors->has('details') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 py-4 font-mono text-sm text-slate-800 outline-none transition duration-200 focus:ring-4">{{ old('details', $setting->details ?? '') }}</textarea>
                @error('details')<p class="mt-2 px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
</div>

<div class="mt-6 flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>{{ $submitLabel }}</button>
    @if (!empty($setting?->id))
        <a href="{{ route('admin.settings.show', $setting->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Setting</a>
    @endif
    <a href="{{ route('admin.settings') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type');
    const typeBadge = document.getElementById('typeBadge');
    const typeDescription = document.getElementById('typeDescription');
    const valueField = document.getElementById('value');
    const valueSingle = document.getElementById('value_single');
    const valueSingleWrap = document.getElementById('valueSingleWrap');
    const valueBooleanWrap = document.getElementById('valueBooleanWrap');
    const valueAreaWrap = document.getElementById('valueAreaWrap');
    const valueAssetWrap = document.getElementById('valueAssetWrap');
    const assetInput = document.getElementById('asset');
    const assetCurrentWrap = document.getElementById('assetCurrentWrap');
    const assetCurrentLink = document.getElementById('assetCurrentLink');
    const assetCurrentPath = document.getElementById('assetCurrentPath');
    const assetImagePreview = document.getElementById('assetImagePreview');
    const assetFilePreview = document.getElementById('assetFilePreview');
    const assetFileName = document.getElementById('assetFileName');
    const assetHelpText = document.getElementById('assetHelpText');
    const valueEditorShell = document.getElementById('valueEditorShell');
    const keyInput = document.getElementById('key');
    const displayNameInput = document.getElementById('display_name');
    const groupInput = document.getElementById('group');
    const docs = @json($typeDocs);
    const storageBaseUrl = @json(asset('storage'));
    const initialAssetUrl = @json($setting->value_url ?? $fallbackAssetUrl);
    let previewObjectUrl = null;

    function createValueEditor() {
        if (!window.CKEDITOR || !valueField || window.CKEDITOR.instances[valueField.id]) {
            return;
        }

        if (typeof window.createResourceCkeditor === 'function') {
            window.createResourceCkeditor(valueField.id, valueField.dataset.ckeditorDirection || 'ltr');
            return;
        }

        window.CKEDITOR.replace(valueField.id, {
            height: 260,
            versionCheck: false,
            removePlugins: 'elementspath',
            resize_enabled: true,
            contentsLangDirection: valueField.dataset.ckeditorDirection || 'ltr',
        });
    }

    function destroyValueEditor() {
        const editor = window.CKEDITOR?.instances?.[valueField?.id];
        if (!editor) {
            return;
        }

        valueField.value = editor.getData();
        editor.destroy(true);
    }

    function revokePreviewObjectUrl() {
        if (!previewObjectUrl) {
            return;
        }

        URL.revokeObjectURL(previewObjectUrl);
        previewObjectUrl = null;
    }

    function syncValueUi() {
        const type = typeSelect.value || 'text';
        const useSingle = ['text', 'number', 'url', 'email', 'color'].includes(type);
        const useBoolean = type === 'boolean';
        const useAsset = ['image', 'file'].includes(type);
        const useRichTextEditor = type === 'rich_text_box';
        valueSingleWrap.classList.toggle('hidden', !useSingle);
        valueBooleanWrap.classList.toggle('hidden', !useBoolean);
        valueAreaWrap.classList.toggle('hidden', useSingle || useBoolean || useAsset);
        valueAssetWrap.classList.toggle('hidden', !useAsset);
        valueEditorShell?.classList.toggle('resource-ckeditor-shell', useRichTextEditor);
        valueField.classList.toggle('font-mono', !useRichTextEditor);

        typeBadge.textContent = type;
        typeDescription.textContent = docs[type] || docs.text;

        if (useSingle) {
            valueSingle.value = valueField.value || '';
        }

        if (assetHelpText) {
            assetHelpText.textContent = type === 'image'
                ? 'Upload one image for this setting. Use another setting if you need another image slot.'
                : 'Upload one file for this setting. Use another setting if you need another downloadable file.';
        }

        if (useRichTextEditor) {
            createValueEditor();
        } else {
            destroyValueEditor();
        }

        renderStoredAsset(type, valueField.value || '', initialAssetUrl);
    }

    function isValidUrl(value) {
        try {
            new URL(value);
            return true;
        } catch (error) {
            return false;
        }
    }

    function buildAssetUrl(path) {
        if (!path) {
            return '';
        }

        if (isValidUrl(path)) {
            return path;
        }

        return `${String(storageBaseUrl).replace(/\/+$/, '')}/${String(path).replace(/^\/+/, '')}`;
    }

    function renderStoredAsset(type, path, fallbackUrl = '') {
        const hasAsset = Boolean(path);
        const finalUrl = fallbackUrl || buildAssetUrl(path);

        assetCurrentWrap?.classList.toggle('hidden', !hasAsset);
        assetCurrentPath.textContent = path || '';

        if (assetCurrentLink) {
            assetCurrentLink.href = finalUrl || '#';
            assetCurrentLink.classList.toggle('hidden', !finalUrl);
        }

        const showImage = type === 'image' && hasAsset;
        const showFile = type === 'file' && hasAsset;

        assetImagePreview?.classList.toggle('hidden', !showImage);
        assetFilePreview?.classList.toggle('hidden', !showFile);

        if (showImage && assetImagePreview) {
            assetImagePreview.src = finalUrl;
        }

        if (showFile && assetFileName) {
            const segments = String(path).split('/');
            const normalizedPath = String(path).replaceAll('\\', '/');
            const normalizedSegments = normalizedPath.split('/');
            assetFileName.textContent = normalizedSegments[normalizedSegments.length - 1] || normalizedPath;
        }
    }

    assetInput?.addEventListener('change', function () {
        const file = assetInput.files?.[0];
        const type = typeSelect.value || 'text';

        if (!file) {
            revokePreviewObjectUrl();
            renderStoredAsset(type, valueField.value || '', initialAssetUrl);
            return;
        }

        revokePreviewObjectUrl();
        previewObjectUrl = URL.createObjectURL(file);
        assetCurrentWrap?.classList.remove('hidden');
        assetCurrentPath.textContent = file.name;

        if (assetCurrentLink) {
            assetCurrentLink.href = previewObjectUrl;
            assetCurrentLink.classList.remove('hidden');
        }

        if (type === 'image') {
            assetImagePreview.src = previewObjectUrl;
            assetImagePreview.classList.remove('hidden');
            assetFilePreview.classList.add('hidden');
        } else {
            assetImagePreview.classList.add('hidden');
            assetFilePreview.classList.remove('hidden');
            assetFileName.textContent = file.name;
        }
    });

    function normalizeKey(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/[^a-z0-9._-]/g, '_')
            .replace(/_+/g, '_')
            .replace(/^[._-]+|[._-]+$/g, '');
    }

    typeSelect?.addEventListener('change', syncValueUi);
    valueSingle?.addEventListener('input', function () {
        valueField.value = valueSingle.value;
    });

    document.querySelectorAll('.boolean-choice').forEach(function (button) {
        button.addEventListener('click', function () {
            valueField.value = button.dataset.booleanValue || '0';
            document.querySelectorAll('.boolean-choice').forEach(function (item) {
                item.classList.remove('border-[#d6ab3d]', 'bg-[#fff8e8]');
            });
            button.classList.add('border-[#d6ab3d]', 'bg-[#fff8e8]');
        });
    });

    if (keyInput && displayNameInput) {
        displayNameInput.addEventListener('input', function () {
            if (keyInput.dataset.touched === '1') {
                return;
            }
            keyInput.value = normalizeKey(displayNameInput.value);
        });

        keyInput.addEventListener('input', function () {
            keyInput.dataset.touched = '1';
            keyInput.value = normalizeKey(keyInput.value);
        });
    }

    syncValueUi();

    document.querySelector('form')?.addEventListener('submit', function () {
        const editor = window.CKEDITOR?.instances?.[valueField?.id];
        if (editor) {
            valueField.value = editor.getData();
            editor.updateElement();
        }
    });

    window.addEventListener('beforeunload', revokePreviewObjectUrl);
});
</script>
