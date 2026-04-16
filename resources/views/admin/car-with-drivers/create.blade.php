@extends('admin.layouts.app')

@section('title', 'Create Car With Driver')
@section('page_title', 'Create Car With Driver')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.car-with-drivers') }}" class="transition hover:text-[#9b7a28]">Car With Driver</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Create Record</span>
    </nav>
@endsection

@push('styles')
<style>
    .cwd-ckeditor-shell .cke { border: 1px solid #e5d7b1 !important; border-radius: 18px !important; overflow: hidden; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04); }
    .cwd-ckeditor-shell .cke_top { border-bottom: 1px solid #f0e6ca !important; background: linear-gradient(180deg, #fffdf8 0%, #fff7e6 100%) !important; padding: 10px 12px !important; }
    .cwd-ckeditor-shell .cke_bottom { border-top: 1px solid #f0e6ca !important; background: #fffaf0 !important; }
    .cwd-ckeditor-shell .cke_contents { background: #fffdf8 !important; }
    .cwd-ckeditor-shell .cke_editable { min-height: 240px; padding: 16px !important; color: #1e293b !important; font-size: 14px !important; }
    .cwd-ckeditor-shell .cke_focus { border-color: #caa23c !important; box-shadow: 0 0 0 4px rgba(247, 233, 181, 0.9) !important; }
    .cwd-ckeditor-shell.is-invalid .cke { border-color: #fca5a5 !important; box-shadow: 0 0 0 4px rgba(254, 226, 226, 0.9) !important; }
    .cwd-car-pill { display: inline-flex; align-items: center; gap: 8px; border-radius: 999px; background: #fff4d6; border: 1px solid #ead39a; color: #7d6220; font-size: 11px; font-weight: 600; padding: 6px 10px; }
    .cwd-car-pill button { display: inline-flex; height: 18px; width: 18px; align-items: center; justify-content: center; border-radius: 999px; border: 0; background: rgba(125, 98, 32, 0.12); color: #7d6220; cursor: pointer; }
    .cwd-car-pill button:hover { background: rgba(125, 98, 32, 0.2); }
</style>
@endpush

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Car With Driver Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">Add New Record</h1>
                            <p class="mt-1 text-sm text-slate-500">Create a car with driver landing section with SEO content, card content, and linked cars.</p>
                        </div>
                        <a href="{{ route('admin.car-with-drivers') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
                            <i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.car-with-drivers.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off" class="space-y-6 p-4 sm:p-6">
                    @include('admin.car-with-drivers._form', ['record' => null, 'submitLabel' => 'Save Record'])
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buildEditorConfig = function (direction) {
            return {
                height: 280,
                versionCheck: false,
                removePlugins: 'elementspath',
                resize_enabled: true,
                contentsLangDirection: direction,
                contentsCss: ['https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap'],
                toolbar: [
                    { name: 'clipboard', items: ['Undo', 'Redo'] },
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                    { name: 'styles', items: ['Styles', 'Format', 'FontSize'] },
                    { name: 'document', items: ['Source'] }
                ]
            };
        };

        if (window.CKEDITOR) {
            CKEDITOR.replace('content_en', buildEditorConfig('ltr'));
            CKEDITOR.replace('content_ar', buildEditorConfig('rtl'));
        }

        const carSelect = document.getElementById('car_ids');
        const carPickerWrap = document.getElementById('car_picker_wrap');
        const carPickerButton = document.getElementById('car_picker_button');
        const carPickerPanel = document.getElementById('car_picker_panel');
        const carPickerSearch = document.getElementById('car_picker_search');
        const carPickerList = document.getElementById('car_picker_list');
        const selectedTags = document.getElementById('selectedCarTags');
        const cardImageInput = document.getElementById('card_image');
        const cardImagePreview = document.getElementById('cardImagePreview');
        const cardImageFileName = document.getElementById('cardImageFileName');
        const defaultCardImage = 'https://placehold.co/200x200/f8e8b2/5e450a?text=Card';

        function getAvailableCars() {
            if (!carSelect) return [];
            const selectedValues = new Set(Array.from(carSelect.selectedOptions).map(option => option.value));
            const query = (carPickerSearch?.value || '').trim().toLowerCase();
            return Array.from(carSelect.options).filter(option => {
                const matchesQuery = query === '' || option.textContent.toLowerCase().includes(query);
                return !selectedValues.has(option.value) && matchesQuery;
            });
        }

        function closeCarPicker() {
            carPickerPanel?.classList.add('hidden');
        }

        function renderPickerList() {
            if (!carPickerList) return;
            const options = getAvailableCars();
            carPickerList.innerHTML = options.length
                ? options.map(option => `<button type="button" class="admin-picker-option car-picker-option" data-car-id="${option.value}">
                        <span class="admin-picker-option-icon"><i class="fa-solid fa-car-side text-sm"></i></span>
                        <span class="min-w-0 flex-1">
                            <span class="admin-picker-option-title block truncate">${option.textContent}</span>
                            <span class="admin-picker-option-subtitle block">Tap to add</span>
                        </span>
                    </button>`).join('')
                : '<div class="admin-picker-empty">No cars found.</div>';
        }

        function renderTags() {
            if (!carSelect || !selectedTags) return;
            const options = Array.from(carSelect.selectedOptions);
            selectedTags.innerHTML = options.length
                ? options.map(option => `<span class="cwd-car-pill">${option.textContent}<button type="button" class="remove-car-tag" data-car-id="${option.value}" aria-label="Remove ${option.textContent}">&times;</button></span>`).join('')
                : '<span class="text-xs text-slate-400">No cars selected yet.</span>';
            renderPickerList();
        }

        carPickerButton?.addEventListener('click', function () {
            carPickerPanel?.classList.toggle('hidden');
            renderPickerList();
            if (!carPickerPanel?.classList.contains('hidden')) {
                carPickerSearch?.focus();
            }
        });

        carPickerSearch?.addEventListener('input', renderPickerList);

        carPickerList?.addEventListener('click', function (event) {
            const button = event.target.closest('.car-picker-option');
            if (!button || !carSelect) return;
            const option = Array.from(carSelect.options).find(item => item.value === button.dataset.carId);
            if (option) {
                option.selected = true;
                renderTags();
            }
            if (carPickerSearch) {
                carPickerSearch.value = '';
            }
            closeCarPicker();
        });

        selectedTags?.addEventListener('click', function (event) {
            const button = event.target.closest('.remove-car-tag');
            if (!button || !carSelect) return;
            const option = Array.from(carSelect.options).find(item => item.value === button.dataset.carId);
            if (option) {
                option.selected = false;
                renderTags();
            }
        });

        document.addEventListener('click', function (event) {
            if (!carPickerWrap?.contains(event.target)) {
                closeCarPicker();
            }
        });

        renderTags();

        cardImageInput?.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) {
                cardImagePreview.src = defaultCardImage;
                cardImageFileName.textContent = 'No file selected';
                return;
            }
            cardImageFileName.textContent = file.name;
            const reader = new FileReader();
            reader.onload = function (e) { cardImagePreview.src = e.target.result; };
            reader.readAsDataURL(file);
        });
    });
</script>
@endpush
